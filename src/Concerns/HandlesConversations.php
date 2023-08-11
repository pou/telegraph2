<?php

namespace DefStudio\Telegraph\Concerns;

use Closure;
use DefStudio\Telegraph\DTO\Conversations\Step;
use DefStudio\Telegraph\DTO\Message;
use DefStudio\Telegraph\Exceptions\StorageException;
use DefStudio\Telegraph\Handlers\Conversation;
use DefStudio\Telegraph\Keyboard\Button;
use DefStudio\Telegraph\Keyboard\Keyboard;

trait HandlesConversations
{
    use HasStorage;

    private const FINALLY_CLOSURE_NAME = '___finally___';
    private const CLEANUP_CLOSURE_NAME = '___cleanup___';

    public Step $step;

    public function getConversationIdentifier(): string
    {
        return 'conversation';
    }

    public function startConversation(Conversation $instance): void
    {
        $instance->chat = $this;
        $instance->start();
    }

    /**
     * @param Conversation $conversation
     * @param Closure|Closure[]|null $next
     * @param string $question
     *
     * @return void
     *
     * @throws \DefStudio\Telegraph\Exceptions\StorageException
     */
    public function storeStep(Conversation $conversation, mixed $next, string $question): void
    {
        $this->step = new Step();

        $this->step->question = $question;

        $preparedConversation = clone $conversation;
        $preparedConversation->chat = null;
        $this->step->conversation = $preparedConversation;

        if (is_array($next)) {
            $variations = [];

            foreach ($next as $answer => $action) {
                if (!is_callable($action)) {
                    continue;
                }
                $preparedAction = clone $action;
                $preparedAction = $preparedAction->bindTo($preparedConversation, $preparedConversation);

                /** @var Closure $preparedAction */
                $variations[$answer] = $preparedAction;
            }

            $this->step->variations = $variations;
        } elseif (is_callable($next)) {
            $preparedNext = clone $next;
            $preparedNext = $preparedNext->bindTo($preparedConversation, $preparedConversation);
            $this->step->next = $preparedNext;
        }

        $this->storage()->set($this->getConversationIdentifier(), $this->step->toArray());
    }

    public function extractStep(): void
    {
        /** @var array{c: string, q: string, n: string, v: string} $stepData */
        $stepData = $this->storage()->get($this->getConversationIdentifier());
        $this->step = Step::fromArray($stepData);
        $this->step->setChat($this);
    }

    public function hasConversation(): bool
    {
        // todo implement storage()->has() method
        return !is_null($this->storage()->get($this->getConversationIdentifier()));
    }

    /**
     * @param array<Closure> $variations
     * @throws StorageException
     */
    public function sendVariations(
        Conversation $conversation,
        string       $question,
        array        $variations,
        ?Closure     $finally = null
    ): void {
        $buttons = [];
        foreach (array_keys($variations) as $title) {
            $buttons[] = Button::make($title)->action($title);
        }

        // todo auto adjust rows, buttons width
        $keyboard = Keyboard::make()->buttons($buttons);

        $response = $this
            ->message($question)
            ->keyboard($keyboard)
            ->send();

        $messageId = $response->telegraphMessageId();
        assert($messageId !== null);

        $variations[self::CLEANUP_CLOSURE_NAME] = function () use ($messageId) {
            /** @var Conversation $this */
            if (isset($this->chat)) {
                $this->chat->deleteKeyboard($messageId)->send();
            }
        };

        if (is_callable($finally)) {
            $variations[self::FINALLY_CLOSURE_NAME] = $finally;
        }

        $this->storeStep($conversation, $variations, $question);
    }

    /**
     * @param array{action: string} $data
     * @throws StorageException
     */
    public function handleConversationVariation(array $data): void
    {
        $this->extractStep();
        $this->storage()->forget($this->getConversationIdentifier());

        if (isset($this->step->variations[self::CLEANUP_CLOSURE_NAME])) {
            $this->processNext($this->step->variations[self::CLEANUP_CLOSURE_NAME]);
        }

        if (isset($this->step->variations[$data['action']])) {
            $this->processNext($this->step->variations[$data['action']]);
        }

        if (isset($this->step->variations[self::FINALLY_CLOSURE_NAME])) {
            $this->processNext($this->step->variations[self::FINALLY_CLOSURE_NAME]);
        }
    }

    public function handleConversation(?Message $message): void
    {
        $this->extractStep();

        if (!empty($this->step->variations)) {
            $this->html(__('telegraph::conversations.reminder_to_choose_variant'))->send();

            return;
        }

        $this->storage()->forget($this->getConversationIdentifier());

        if (isset($this->step->next)) {
            $this->processNext($this->step->next, $message);
        }
    }

    private function processNext(Closure $next, ?Message $data = null): void
    {
        $next = $next->bindTo($this->step->conversation, $this->step->conversation);

        assert(is_callable($next));

        if (is_null($data)) {
            $next();
        } else {
            $next($data);
        }
    }
}

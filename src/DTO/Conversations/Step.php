<?php

namespace DefStudio\Telegraph\DTO\Conversations;

use Closure;
use DefStudio\Telegraph\Concerns\SerializesClosure;
use DefStudio\Telegraph\Handlers\Conversation;
use DefStudio\Telegraph\Models\TelegraphChat;
use Illuminate\Contracts\Support\Arrayable;

final class Step implements Arrayable
{
    use SerializesClosure;

    public ?Conversation $conversation;
    public string $question;
    public ?Closure $next = null;
    /** @var array|Closure[] */
    public array $variations = [];

    public bool $deleteQuestion = true;
    public bool $deleteQuestionButtons = false;

    public function setChat(TelegraphChat $chat): void
    {
        assert($this->conversation !== null);

        $this->conversation->chat = $chat;
    }

    /**
     * @param array{c:string, q:string, n:string|null, v:string} $data
     */
    public static function fromArray(array $data): self
    {
        $instance = new self();

        /** @var Conversation $conversation */
        $conversation = unserialize($data['c']);
        $instance->conversation = $conversation;

        /** @var string $question */
        $question = unserialize($data['q']);
        $instance->question = $question;

        if (is_string($data['n'])) {
            $next = $instance->unserializeClosure($data['n']);
            $instance->next = $next->bindTo($instance->conversation, $instance->conversation);
        }

        /** @var array<Closure> $variations */
        $variations = unserialize($data['v']);

        /** @var string $variation */
        foreach ($variations as $answer => $variation) {
            $instance->variations[$answer] = $instance->unserializeClosure($variation);
        }

        return $instance;
    }

    /**
     * @return array{c:string, q:string, n:string|null, v:string}
     */
    public function toArray(): array
    {
        $variations = [];

        if (!empty($this->variations)) {
            /** @var Closure $variation */
            foreach ($this->variations as $answer => $variation) {
                $variations[$answer] = $this->serializeClosure($variation);
            }
        }

        return [
            'c' => serialize($this->conversation),
            'q' => serialize($this->question),
            'n' => is_callable($this->next)
                ? $this->serializeClosure($this->next)
                : null,
            'v' => serialize($variations),
        ];
    }
}

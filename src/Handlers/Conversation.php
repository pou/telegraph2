<?php

namespace DefStudio\Telegraph\Handlers;

use Closure;
use DefStudio\Telegraph\DTO\Invoice;
use DefStudio\Telegraph\Models\TelegraphChat;

abstract class Conversation
{
    public ?TelegraphChat $chat;

    /**
     * Number of minutes this specific conversation should be cached.
     * @var int
     */
    protected int $cacheTime;

    public function ask(string $question, Closure $next/*, $additionalParameters = []*/): void
    {
        $this->chat->message($question)->send();
        $this->chat->storeConversation($this, $next, $question/*, $additionalParameters*/);
    }

    public function repeat(string $question = null): void
    {
        $step = $this->chat->step;

        $question = $question ?? $step->question;
        $next = $step->next;

        $this->ask($question, $next);
    }

    public function say(string $message): void
    {
        $this->message($message);
    }

    public function message($message): void
    {
        $this->chat->message($message)->send();
    }

    public function html($message): void
    {
        $this->chat->html($message)->send();
    }

    public function invoice(Invoice $invoice, ?Closure $handler = null): void
    {
        $this->chat->invoice($invoice, $handler)->send();
    }

    /**
     * Should the conversation be skipped (temporarily).
     * @param  IncomingMessage $message
     * @return bool
     */
    public function skipsConversation(IncomingMessage $message)
    {
        //
    }

    /**
     * Should the conversation be removed and stopped (permanently).
     * @param  IncomingMessage $message
     * @return bool
     */
    public function stopsConversation(IncomingMessage $message)
    {
        //
    }

    /**
     * Override default conversation cache time (only for this conversation).
     * @return mixed
     */
    public function getConversationCacheTime()
    {
        return $this->cacheTime ?? null;
    }

    abstract public function start(): void;

    /**
     * @return array
     */
    public function __sleep()
    {
        $properties = get_object_vars($this);
        if (!$this instanceof ShouldQueue) {
            unset($properties['bot']);
        }

        return array_keys($properties);
    }
}

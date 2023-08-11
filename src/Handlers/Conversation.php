<?php

namespace DefStudio\Telegraph\Handlers;

use Closure;
use DefStudio\Telegraph\DTO\Invoice;
use DefStudio\Telegraph\Models\TelegraphChat;

abstract class Conversation
{
    public ?TelegraphChat $chat;

    public function ask(string $question, ?Closure $next): void
    {
        assert($this->chat !== null);

        $this->chat->message($question)->send();
        $this->chat->storeStep($this, $next, $question);
    }

    public function repeat(string $question = null): void
    {
        assert($this->chat !== null);

        $step = $this->chat->step;

        $question = $question ?? $step->question;
        $next = $step->next;

        $this->ask($question, $next);
    }

    /**
     * @param array<Closure> $variations
     */
    public function variations(
        string $question,
        array  $variations,
        ?Closure $finally = null
    ): void {
        assert($this->chat !== null);

        $this->chat->sendVariations($this, $question, $variations, $finally);
    }

    public function yesOrNo(
        string  $question,
        Closure $yes,
        Closure $no,
        ?Closure $finally = null,
        string  $yesTitle = 'Да',
        string  $noTitle = 'Нет',
    ): void {
        $this->variations($question, [
            $yesTitle => $yes,
            $noTitle => $no,
        ], $finally);
    }

    public function say(string $message): void
    {
        assert($this->chat !== null);
        $this->chat->html($message)->send();
    }

    public function invoice(Invoice $invoice, ?Closure $handler = null): void
    {
        assert($this->chat !== null);
        $this->chat->invoice($invoice, $handler)->send();
    }

    abstract public function start(): void;
}

<?php

namespace DefStudio\Telegraph\DTO\Conversations;

use Closure;
use DefStudio\Telegraph\Concerns\SerializesClosure;
use DefStudio\Telegraph\Handlers\Conversation;
use DefStudio\Telegraph\Models\TelegraphChat;
use Illuminate\Contracts\Support\Arrayable;

class Step implements Arrayable
{
    use SerializesClosure;

    public ?Conversation $conversation;
    public string $question;
    public ?Closure $next;

    public static function fromArray(array $data): static
    {
        $instance = new static();

        $instance->conversation = unserialize($data['c']);
        $instance->question = unserialize($data['q']);

        $next = $instance->unserializeClosure($data['n']);
        $instance->next = $next->bindTo($instance->conversation, $instance->conversation);

        return $instance;
    }

    public function setChat(TelegraphChat $chat): void
    {
        $this->conversation->chat = $chat;

        $this->next = $this->next->bindTo($this->conversation, $this->conversation);
    }

    public function toArray(): array
    {
        return [
            'c' => serialize($this->conversation),
            'q' => serialize($this->question),
            'n' => $this->serializeClosure($this->next),
        ];
    }
}

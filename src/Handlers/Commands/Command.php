<?php

namespace DefStudio\Telegraph\Handlers\Commands;

use DefStudio\Telegraph\Models\TelegraphChat;

abstract class Command
{
    public string $command;
    public string $parameter;

    public bool $stopsConversation = false;

    /**
     * @param TelegraphChat $chat
     * @return void
     */
    abstract public function handle($chat): void;
}

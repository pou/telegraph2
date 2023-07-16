<?php

namespace DefStudio\Telegraph\Handlers\Commands;

use DefStudio\Telegraph\Models\TelegraphChat;

abstract class Command
{
    public string $command;
    public string $parameter;

    public bool $stopsConversation = false;

    abstract public function handle(TelegraphChat $chat): void;
}

<?php

namespace DefStudio\Telegraph\Handlers\Commands;

use DefStudio\Telegraph\Handlers\WebhookHandler;
use DefStudio\Telegraph\Models\TelegraphChat;

class StubCommand extends Command
{
    public WebhookHandler $handler;

    public function handle(TelegraphChat $chat): void
    {
        if (!$this->handler->canHandle($this->command)) {
            $this->handler->handleUnknownCommand($this);

            return;
        }

        $command = $this->command;
        $this->handler->$command();
    }
}

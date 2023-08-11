<?php

namespace DefStudio\Telegraph\Handlers\Commands;

use DefStudio\Telegraph\Handlers\WebhookHandler;
use DefStudio\Telegraph\Models\TelegraphChat;

class StubCommand extends Command
{
    public WebhookHandler $handler;

    /**
     * @param TelegraphChat $chat
     * @return void
     */
    public function handle($chat): void
    {
        if (!$this->handler->canHandle($this->command)) {
            $this->handler->handleUnknownCommand($this);

            return;
        }

        $command = $this->command;
        $this->handler->$command();
    }
}

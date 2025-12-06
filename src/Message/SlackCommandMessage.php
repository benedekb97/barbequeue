<?php

declare(strict_types=1);

namespace App\Message;

use App\Slack\Command\Component\SlackCommand;

readonly class SlackCommandMessage
{
    public function __construct(
        private SlackCommand $command,
    ) {
    }

    public function getCommand(): SlackCommand
    {
        return $this->command;
    }
}

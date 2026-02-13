<?php

declare(strict_types=1);

namespace App\Message\Slack;

use App\Slack\Command\SlackCommand;

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

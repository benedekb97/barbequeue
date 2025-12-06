<?php

declare(strict_types=1);

namespace App\Slack\Command\Component\Exception;

use App\Slack\Command\Command;
use App\Slack\Command\SubCommand;
use Exception;

class InvalidSubCommandException extends Exception
{
    public function __construct(
        private readonly Command $command,
        private readonly SubCommand $subCommand
    ) {
        parent::__construct(
            'Sub-command '.$this->subCommand->value.' is not compatible with command '.$command->value
        );
    }

    public function getCommand(): Command
    {
        return $this->command;
    }

    public function getSubCommand(): SubCommand
    {
        return $this->subCommand;
    }
}

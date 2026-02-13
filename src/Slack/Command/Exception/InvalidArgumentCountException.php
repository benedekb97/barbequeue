<?php

declare(strict_types=1);

namespace App\Slack\Command\Exception;

use App\Slack\Command\Command;
use App\Slack\Command\SubCommand;

class InvalidArgumentCountException extends \Exception
{
    public function __construct(
        private readonly Command $command,
        private readonly ?SubCommand $subCommand,
    ) {
        parent::__construct(
            'Invalid number of arguments provided for command '.$command->value.' '.$subCommand?->value
        );
    }

    public function getCommand(): Command
    {
        return $this->command;
    }

    public function getSubCommand(): ?SubCommand
    {
        return $this->subCommand;
    }
}

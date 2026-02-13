<?php

declare(strict_types=1);

namespace App\Slack\Command\Exception;

use App\Slack\Command\Command;
use App\Slack\Command\SubCommand;

class InvalidSubCommandException extends \Exception
{
    public function __construct(
        private readonly Command $command,
        private readonly ?SubCommand $subCommand = null,
        private readonly ?string $subCommandText = null,
    ) {
        parent::__construct(
            'Sub-command '.($this->subCommand->value ?? $this->subCommandText).' is not compatible with command '.$command->value
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

    public function getSubCommandText(): ?string
    {
        return $this->subCommand->value ?? $this->subCommandText;
    }
}

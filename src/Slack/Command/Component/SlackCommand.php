<?php

declare(strict_types=1);

namespace App\Slack\Command\Component;

use App\Slack\Command\Command;
use App\Slack\Command\Component\Exception\InvalidArgumentCountException;
use App\Slack\Command\SubCommand;
use InvalidArgumentException;

class SlackCommand
{
    private bool $pending = true;

    public function __construct(
        private readonly Command $command,
        private readonly array $arguments,
        private readonly ?SubCommand $subCommand = null,
    ) {}

    public function getCommand(): Command
    {
        return $this->command;
    }

    public function getSubCommand(): ?SubCommand
    {
        return $this->subCommand;
    }

    public function getArguments(): array
    {
        return $this->arguments;
    }

    public function getArgument(string $key): string
    {
        return $this->arguments[$key] ?? throw new InvalidArgumentException(
            sprintf(
                'Argument %s does not exist on command %s. Available arguments: %s',
                $key,
                $this->command->value,
                implode(', ', $this->command->getArguments($this->subCommand))
            )
        );
    }

    public function isPending(): bool
    {
        return $this->pending;
    }
}

<?php

declare(strict_types=1);

namespace App\Slack\Command\Validator\Argument;

use App\Slack\Command\Command;
use App\Slack\Command\Exception\InvalidArgumentCountException;
use App\Slack\Command\Exception\SubCommandMissingException;
use App\Slack\Command\SubCommand;

readonly class ArgumentCountValidator
{
    /** @throws InvalidArgumentCountException|SubCommandMissingException */
    public function validate(Command $command, ?SubCommand $subCommand, array $arguments): void
    {
        if (count($arguments) >= $command->getRequiredArgumentCount($subCommand)) {
            return;
        }

        throw new InvalidArgumentCountException($command, $subCommand);
    }
}

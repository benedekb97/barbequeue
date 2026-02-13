<?php

declare(strict_types=1);

namespace App\Slack\Command\Validator;

use App\Slack\Command\Command;
use App\Slack\Command\Exception\InvalidSubCommandException;
use App\Slack\Command\SubCommand;

readonly class SubCommandValidator
{
    /** @throws InvalidSubCommandException */
    public function validate(Command $command, ?SubCommand $subCommand, string $subCommandText): void
    {
        if (!$command->isSubCommandRequired()) {
            return;
        }

        if (null === $subCommand) {
            throw new InvalidSubCommandException($command, subCommandText: $subCommandText);
        }

        if (in_array($subCommand, $command->getSubCommands())) {
            return;
        }

        throw new InvalidSubCommandException($command, $subCommand);
    }
}

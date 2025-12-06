<?php

declare(strict_types=1);

namespace App\Slack\Command;

use App\Slack\Command\Component\Exception\SubCommandMissingException;

enum Command: string
{
    case BBQ = 'bbq';
    case BBQ_ADMIN = 'bbq-admin';

    /** @throws SubCommandMissingException */
    public function getRequiredArgumentCount(?SubCommand $subCommand): int
    {
        return count($this->getRequiredArguments($subCommand));
    }

    public function getRequiredArguments(?SubCommand $subCommand): array
    {
        return match ($this) {
            self::BBQ => match ($subCommand) {
                null, SubCommand::JOIN, SubCommand::LEAVE => ['queue'],
                default => [],
            },
            self::BBQ_ADMIN => match ($subCommand) {
                null => throw new SubCOmmandMissingException($this),
                SubCommand::ADD, SubCommand::LEAVE => ['user'],
                default => [],
            },
        };
    }

    public function getOptionalArguments(?SubCommand $subCommand): array
    {
        return match ($this) {
            default => [],
        };
    }

    public function getArguments(?SubCommand $subCommand): array
    {
        return array_merge($this->getRequiredArguments($subCommand), $this->getOptionalArguments($subCommand));
    }

    public function hasSubCommands(): bool
    {
        return !empty($this->getSubCommands());
    }

    public function isSubCommandRequired(): bool
    {
        return match ($this) {
            self::BBQ => false,
            self::BBQ_ADMIN => true,
        };
    }

    public function getSubCommands(): ?array
    {
        return match ($this) {
            self::BBQ => [SubCommand::JOIN, SubCommand::LEAVE],
            self::BBQ_ADMIN => [SubCommand::ADD, SubCommand::LEAVE],
        };
    }
}

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

    /**
     * @return array|string[]
     *
     * @throws SubCommandMissingException
     */
    public function getRequiredArguments(?SubCommand $subCommand): array
    {
        return match ($this) {
            self::BBQ => match ($subCommand) {
                null, SubCommand::JOIN, SubCommand::LEAVE => ['queue'],
                default => [],
            },
            self::BBQ_ADMIN => match ($subCommand) {
                null => throw new SubCommandMissingException($this),
                SubCommand::ADD, SubCommand::LEAVE => ['user'],
                default => [],
            },
        };
    }

    /** @return array|string[] */
    public function getOptionalArguments(?SubCommand $subCommand): array
    {
        return match ($this) {
            default => [],
        };
    }

    /**
     * @return array|string[]
     *
     * @throws SubCommandMissingException
     */
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

    /** @return array|SubCommand[] */
    public function getSubCommands(): array
    {
        return match ($this) {
            self::BBQ => [SubCommand::JOIN, SubCommand::LEAVE],
            self::BBQ_ADMIN => [SubCommand::ADD, SubCommand::LEAVE],
        };
    }

    public function getUsage(?SubCommand $subCommand): string
    {
        if ($subCommand === null) {
            return sprintf(
                '/%s %s %s',
                $this->value,
                implode(
                    ' ',
                    array_map(
                        fn (string $argument) => "\{$argument\}",
                        $this->getRequiredArguments($subCommand)
                    )
                ),
                implode(
                    ' ',
                    array_map(
                        fn (string $argument) => "\{?$argument\}",
                        $this->getOptionalArguments($subCommand)
                    )
                ),
            );
        }

        return sprintf(
            '/%s %s %s %s',
            $this->value,
            $subCommand->value,
            implode(
                ' ',
                array_map(
                    fn (string $argument) => "\{$argument\}",
                    $this->getRequiredArguments($subCommand)
                )
            ),
            implode(
                ' ',
                array_map(
                    fn (string $argument) => "\{?$argument\}",
                    $this->getOptionalArguments($subCommand)
                )
            )
        );
    }

    public function getUsages(): array
    {
        $usages = [];

        foreach ($this->getSubCommands() as $subCommand) {
            $usages[] = $this->getUsage($subCommand);
        }

        return $usages;
    }
}

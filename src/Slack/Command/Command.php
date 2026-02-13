<?php

declare(strict_types=1);

namespace App\Slack\Command;

use App\Slack\Common\Component\AuthorisableInterface;

enum Command: string implements AuthorisableInterface
{
    case BBQ = 'bbq';
    case BBQ_ADMIN = 'bbq-admin';

    /** Test command used to test functionality for command with no sub-commands */
    case TEST = 'test';

    public function getRequiredArgumentCount(?SubCommand $subCommand): int
    {
        return count($this->getRequiredArguments($subCommand));
    }

    /** @return array|CommandArgument[] */
    public function getRequiredArguments(?SubCommand $subCommand): array
    {
        return match ($this) {
            self::BBQ => match ($subCommand) {
                SubCommand::JOIN, SubCommand::LEAVE, SubCommand::LIST => [CommandArgument::QUEUE],
                default => [],
            },
            self::BBQ_ADMIN => match ($subCommand) {
                SubCommand::ADD_USER,
                SubCommand::REMOVE_USER => [CommandArgument::USER],

                SubCommand::EDIT_QUEUE,
                SubCommand::POP_QUEUE => [CommandArgument::QUEUE],

                SubCommand::EDIT_REPOSITORY,
                SubCommand::REMOVE_REPOSITORY => [CommandArgument::REPOSITORY],

                default => [],
            },
            self::TEST => [],
        };
    }

    /** @return array|CommandArgument[] */
    public function getOptionalArguments(?SubCommand $subCommand): array
    {
        return match ($this) {
            self::BBQ => match ($subCommand) {
                SubCommand::JOIN => [CommandArgument::TIME],
                SubCommand::HELP => [CommandArgument::COMMAND],
                default => [],
            },
            self::BBQ_ADMIN => match ($subCommand) {
                SubCommand::HELP => [CommandArgument::COMMAND],
                default => [],
            },
            default => [],
        };
    }

    /** @return array|CommandArgument[] */
    public function getArguments(?SubCommand $subCommand): array
    {
        return array_merge($this->getRequiredArguments($subCommand), $this->getOptionalArguments($subCommand));
    }

    public function hasArgument(?SubCommand $subCommand, CommandArgument $argument): bool
    {
        return in_array($argument, $this->getArguments($subCommand), true);
    }

    public function hasSubCommands(): bool
    {
        return !empty($this->getSubCommands());
    }

    public function isSubCommandRequired(): bool
    {
        return match ($this) {
            self::BBQ, self::BBQ_ADMIN => true,
            self::TEST => false,
        };
    }

    public function getExample(?SubCommand $subCommand): ?string
    {
        return match ($this) {
            self::BBQ_ADMIN => match ($subCommand) {
                SubCommand::ADD_USER => '/bbq-admin add-user @Bob Example',
                SubCommand::REMOVE_USER => '/bbq-admin remove-user @Bob Example',
                default => null,
            },
            self::BBQ => match ($subCommand) {
                SubCommand::JOIN => '/bbq join staging 1h 20m',
                default => null,
            },
            default => null,
        };
    }

    /** @return array|SubCommand[] */
    public function getSubCommands(): array
    {
        return match ($this) {
            self::BBQ => [
                SubCommand::JOIN,
                SubCommand::LEAVE,
                SubCommand::LIST,
                SubCommand::CONFIGURE,
                SubCommand::HELP,
            ],
            self::BBQ_ADMIN => [
                SubCommand::ADD_USER,
                SubCommand::REMOVE_USER,

                SubCommand::ADD_QUEUE,
                SubCommand::EDIT_QUEUE,
                SubCommand::POP_QUEUE,

                SubCommand::ADD_REPOSITORY,
                SubCommand::EDIT_REPOSITORY,
                SubCommand::REMOVE_REPOSITORY,
                SubCommand::LIST_REPOSITORIES,

                SubCommand::HELP,
            ],
            self::TEST => [],
        };
    }

    public function getUsage(?SubCommand $subCommand): string
    {
        $exampleText = ($example = $this->getExample($subCommand)) ? ' Example: `'.$example.'`' : '';

        $requiredArguments = $this->getRequiredArguments($subCommand);
        $optionalArguments = $this->getOptionalArguments($subCommand);

        $requiredArgumentText = implode(
            ' ',
            array_map(
                fn (CommandArgument $argument) => '{'.$argument->value.'}',
                $requiredArguments
            )
        );

        $optionalArgumentText = implode(
            ' ',
            array_map(
                fn (CommandArgument $argument) => '{?'.$argument->value.'}',
                $optionalArguments,
            )
        );

        $command = array_filter([
            $this->value,
            $subCommand?->value,
            $requiredArgumentText,
            $optionalArgumentText,
        ]);

        $commandText = sprintf('`/%s`', implode(' ', $command));

        if (!empty($exampleText)) {
            $commandText .= $exampleText;
        }

        return $commandText;
    }

    public function getHelpText(?SubCommand $subCommand): string
    {
        return match ($this) {
            self::BBQ_ADMIN => match ($subCommand) {
                SubCommand::LIST_REPOSITORIES => 'List repositories added to workspace',
                SubCommand::ADD_REPOSITORY => 'Add a repository to your workspace',
                SubCommand::EDIT_REPOSITORY => 'Modify a repository in your workspace',
                SubCommand::REMOVE_REPOSITORY => 'Remove a repository from your workspace',

                SubCommand::ADD_USER => 'Add a user as an administrator',
                SubCommand::REMOVE_USER => 'Remove an administrator from your workspace',

                SubCommand::ADD_QUEUE => 'Add a queue to your workspace',
                SubCommand::EDIT_QUEUE => 'Configure a queue in your workspace',
                SubCommand::POP_QUEUE => 'Remove the first person from a queue',

                SubCommand::HELP => 'Display this message',

                default => '',
            },
            self::BBQ => match ($subCommand) {
                SubCommand::LIST => 'Show a list of people currently in a queue',
                SubCommand::JOIN => 'Join the back of a queue. Specify the amount of time you will hold the queue for by adding a time to the end of the command',
                SubCommand::LEAVE => 'Remove yourself from a queue',
                SubCommand::CONFIGURE => 'Configure your personal preferences in BBQ',

                SubCommand::HELP => 'Display this message',

                default => '',
            },
            default => '',
        };
    }

    public function getUsages(): array
    {
        $usages = [];

        foreach ($this->getSubCommands() as $subCommand) {
            $usages[] = $this->getUsage($subCommand);
        }

        return $usages;
    }

    public function isAuthorisationRequired(): bool
    {
        return match ($this) {
            self::BBQ_ADMIN => true,
            default => false,
        };
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Command;

use App\Slack\Command\Command;
use App\Slack\Command\CommandArgument;
use App\Slack\Command\SubCommand;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(Command::class)]
class CommandTest extends KernelTestCase
{
    #[Test, DataProvider('provideForItShouldReturnRequiredArgumentCount')]
    public function itShouldReturnRequiredArguments(
        Command $command,
        ?SubCommand $subCommand,
        ?array $arguments,
    ): void {
        $this->assertEquals($arguments, $command->getRequiredArguments($subCommand));
        $this->assertNotNull($arguments);
        $this->assertEquals(count($arguments), $command->getRequiredArgumentCount($subCommand));
    }

    public static function provideForItShouldReturnRequiredArgumentCount(): array
    {
        return [
            [Command::BBQ, null, []],
            [Command::BBQ, SubCommand::JOIN, [CommandArgument::QUEUE]],
            [Command::BBQ, SubCommand::LEAVE, [CommandArgument::QUEUE]],
            [Command::BBQ_ADMIN, null, []],
            [Command::BBQ_ADMIN, SubCommand::ADD_USER, [CommandArgument::USER]],
            [Command::BBQ_ADMIN, SubCommand::REMOVE_USER, [CommandArgument::USER]],
            [Command::BBQ_ADMIN, SubCommand::EDIT_QUEUE, [CommandArgument::QUEUE]],
            [Command::BBQ_ADMIN, SubCommand::POP_QUEUE, [CommandArgument::QUEUE]],
            [Command::BBQ_ADMIN, SubCommand::ADD_REPOSITORY, []],
            [Command::BBQ_ADMIN, SubCommand::EDIT_REPOSITORY, [CommandArgument::REPOSITORY]],
            [Command::BBQ_ADMIN, SubCommand::REMOVE_REPOSITORY, [CommandArgument::REPOSITORY]],
            [Command::BBQ_ADMIN, SubCommand::LIST_REPOSITORIES, []],
        ];
    }

    #[Test, DataProvider('provideForItShouldReturnOptionalArguments')]
    public function itShouldReturnOptionalArguments(
        Command $command,
        ?SubCommand $subCommand,
        array $optionalArguments,
    ): void {
        $this->assertEquals($optionalArguments, $command->getOptionalArguments($subCommand));
    }

    public static function provideForItShouldReturnOptionalArguments(): iterable
    {
        return [
            [Command::BBQ, null, []],
            [Command::BBQ, SubCommand::JOIN, [CommandArgument::TIME]],
            [Command::BBQ, SubCommand::LEAVE, []],
            [Command::BBQ_ADMIN, null, []],
            [Command::BBQ_ADMIN, SubCommand::ADD_USER, []],
            [Command::BBQ_ADMIN, SubCommand::REMOVE_USER, []],
            [Command::BBQ_ADMIN, SubCommand::ADD_REPOSITORY, []],
            [Command::BBQ_ADMIN, SubCommand::EDIT_REPOSITORY, []],
            [Command::BBQ_ADMIN, SubCommand::REMOVE_REPOSITORY, []],
            [Command::BBQ_ADMIN, SubCommand::POP_QUEUE, []],
            [Command::BBQ_ADMIN, SubCommand::EDIT_QUEUE, []],
        ];
    }

    #[Test, DataProvider('provideForItShouldRequireSubCommands')]
    public function itShouldRequireSubCommands(Command $command, bool $required): void
    {
        $this->assertEquals($required, $command->isSubCommandRequired());
    }

    public static function provideForItShouldRequireSubCommands(): array
    {
        return [
            [Command::BBQ, true],
            [Command::BBQ_ADMIN, true],
            [Command::TEST, false],
        ];
    }

    #[Test, DataProvider('provideForItShouldHaveSubCommands')]
    public function itShouldHaveSubCommands(Command $command, bool $hasSubCommands): void
    {
        $this->assertEquals($hasSubCommands, $command->hasSubCommands());
    }

    public static function provideForItShouldHaveSubCommands(): array
    {
        return [
            [Command::BBQ, true],
            [Command::BBQ_ADMIN, true],
            [Command::TEST, false],
        ];
    }

    #[Test, DataProvider('provideAllSubCommandUsages')]
    public function itShouldReturnCorrectUsageForAllSubCommands(
        Command $command,
        SubCommand $subCommand,
        string $usage,
    ): void {
        $this->assertStringContainsString($usage, $command->getUsage($subCommand));
    }

    public static function provideAllSubCommandUsages(): array
    {
        return [
            [Command::BBQ, SubCommand::JOIN, '/bbq join {queue}'],
            [Command::BBQ, SubCommand::LEAVE, '/bbq leave {queue}'],
            [Command::BBQ, SubCommand::LIST, '/bbq list {queue}'],
            [Command::BBQ_ADMIN, SubCommand::POP_QUEUE, '/bbq-admin pop-queue {queue}'],
            [Command::BBQ_ADMIN, SubCommand::EDIT_QUEUE, '/bbq-admin edit-queue {queue}'],
            [Command::BBQ_ADMIN, SubCommand::ADD_USER, '/bbq-admin add-user {user}'],
            [Command::BBQ_ADMIN, SubCommand::REMOVE_USER, '/bbq-admin remove-user {user}'],
            [Command::BBQ_ADMIN, SubCommand::ADD_REPOSITORY, '/bbq-admin add-repository'],
            [Command::BBQ_ADMIN, SubCommand::EDIT_REPOSITORY, '/bbq-admin edit-repository {repository}'],
            [Command::BBQ_ADMIN, SubCommand::REMOVE_REPOSITORY, '/bbq-admin remove-repository {repository}'],
            [Command::BBQ_ADMIN, SubCommand::LIST_REPOSITORIES, '/bbq-admin list-repositories'],
        ];
    }

    #[Test, DataProvider('provideAllCommandUsages')]
    public function itShouldReturnCorrectUsagesForAllCommands(
        Command $command,
        array $usages,
    ): void {
        $this->assertEquals($usages, $command->getUsages());
    }

    public static function provideAllCommandUsages(): array
    {
        return [
            [Command::BBQ, [
                '`/bbq join {queue} {?time}` Example: `/bbq join staging 1h 20m`',
                '`/bbq leave {queue}`',
                '`/bbq list {queue}`',
                '`/bbq configure`',
                '`/bbq help {?command}`',
            ]],
            [Command::BBQ_ADMIN, [
                '`/bbq-admin add-user {user}` Example: `/bbq-admin add-user @Bob Example`',
                '`/bbq-admin remove-user {user}` Example: `/bbq-admin remove-user @Bob Example`',
                '`/bbq-admin add-queue`',
                '`/bbq-admin edit-queue {queue}`',
                '`/bbq-admin pop-queue {queue}`',
                '`/bbq-admin add-repository`',
                '`/bbq-admin edit-repository {repository}`',
                '`/bbq-admin remove-repository {repository}`',
                '`/bbq-admin list-repositories`',
                '`/bbq-admin help {?command}`',
            ]],
        ];
    }

    #[Test, DataProvider('provideAllAuthorisationRequiredCases')]
    public function itShouldReturnCorrectAuthorisationRequired(Command $command, bool $isAuthorisationRequired): void
    {
        $this->assertEquals($isAuthorisationRequired, $command->isAuthorisationRequired());
    }

    public static function provideAllAuthorisationRequiredCases(): array
    {
        return [
            [Command::BBQ, false],
            [Command::BBQ_ADMIN, true],
            [Command::TEST, false],
        ];
    }

    #[Test, DataProvider('provideHelpTexts')]
    public function itShouldReturnCorrectHelpText(Command $command, SubCommand $subCommand, string $text): void
    {
        $this->assertEquals($text, $command->getHelpText($subCommand));
    }

    public static function provideHelpTexts(): array
    {
        return [
            [Command::BBQ, SubCommand::JOIN, 'Join the back of a queue. Specify the amount of time you will hold the queue for by adding a time to the end of the command'],
            [Command::BBQ, SubCommand::LEAVE, 'Remove yourself from a queue'],
            [Command::BBQ, SubCommand::LIST, 'Show a list of people currently in a queue'],
            [Command::BBQ, SubCommand::HELP, 'Display this message'],
            [Command::BBQ_ADMIN, SubCommand::LIST_REPOSITORIES, 'List repositories added to workspace'],
            [Command::BBQ_ADMIN, SubCommand::ADD_REPOSITORY, 'Add a repository to your workspace'],
            [Command::BBQ_ADMIN, SubCommand::EDIT_REPOSITORY, 'Modify a repository in your workspace'],
            [Command::BBQ_ADMIN, SubCommand::REMOVE_REPOSITORY, 'Remove a repository from your workspace'],
            [Command::BBQ_ADMIN, SubCommand::ADD_USER, 'Add a user as an administrator'],
            [Command::BBQ_ADMIN, SubCommand::REMOVE_USER, 'Remove an administrator from your workspace'],
            [Command::BBQ_ADMIN, SubCommand::EDIT_QUEUE, 'Configure a queue in your workspace'],
            [Command::BBQ_ADMIN, SubCommand::POP_QUEUE, 'Remove the first person from a queue'],
            [Command::BBQ_ADMIN, SubCommand::HELP, 'Display this message'],
        ];
    }
}

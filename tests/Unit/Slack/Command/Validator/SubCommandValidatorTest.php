<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Command\Validator;

use App\Slack\Command\Command;
use App\Slack\Command\Exception\InvalidSubCommandException;
use App\Slack\Command\SubCommand;
use App\Slack\Command\Validator\SubCommandValidator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(SubCommandValidator::class)]
class SubCommandValidatorTest extends KernelTestCase
{
    #[Test]
    public function itShouldNotThrowExceptionIfCommandDoesNotRequireSubCommand(): void
    {
        $this->expectNotToPerformAssertions();

        $validator = new SubCommandValidator();

        $validator->validate(Command::TEST, null, '');
    }

    #[Test]
    public function itShouldThrowInvalidSubCommandExceptionIfSubCommandRequiredAndNoneProvided(): void
    {
        $validator = new SubCommandValidator();

        $command = Command::BBQ;
        $subCommandText = 'invalid-sub-command';

        $this->expectException(InvalidSubCommandException::class);

        try {
            $validator->validate($command, null, $subCommandText);
        } catch (InvalidSubCommandException $exception) {
            $this->assertSame($command, $exception->getCommand());
            $this->assertSame($subCommandText, $exception->getSubCommandText());
            $this->assertNull($exception->getSubCommand());

            throw $exception;
        }
    }

    #[Test]
    public function itShouldThrowInvalidSubCommandExceptionIfSubCommandNotApplicableToCommand(): void
    {
        $validator = new SubCommandValidator();

        $command = Command::BBQ;
        $subCommand = SubCommand::EDIT_QUEUE;
        $subCommandText = $subCommand->value;

        $this->expectException(InvalidSubCommandException::class);

        try {
            $validator->validate($command, $subCommand, $subCommandText);
        } catch (InvalidSubCommandException $exception) {
            $this->assertSame($command, $exception->getCommand());
            $this->assertSame($subCommandText, $exception->getSubCommandText());
            $this->assertSame($subCommand, $exception->getSubCommand());

            throw $exception;
        }
    }

    #[Test, DataProvider('provideAllCommandSubCommandCombinations')]
    public function itShouldNotThrowExceptionIfSubCommandValidForCommand(Command $command, ?SubCommand $subCommand): void
    {
        $this->expectNotToPerformAssertions();

        $validator = new SubCommandValidator();

        $validator->validate($command, $subCommand, $subCommand->value ?? '');
    }

    public static function provideAllCommandSubCommandCombinations(): iterable
    {
        foreach (Command::cases() as $command) {
            foreach ($command->getSubCommands() as $subCommand) {
                yield [$command, $subCommand];
            }
        }
    }
}

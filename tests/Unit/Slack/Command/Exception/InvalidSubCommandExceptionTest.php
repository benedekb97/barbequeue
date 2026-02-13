<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Command\Exception;

use App\Slack\Command\Command;
use App\Slack\Command\Exception\InvalidSubCommandException;
use App\Slack\Command\SubCommand;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(InvalidSubCommandException::class)]
class InvalidSubCommandExceptionTest extends KernelTestCase
{
    #[Test]
    public function itShouldReturnPassedProperties(): void
    {
        $command = Command::BBQ;
        $subCommand = SubCommand::LEAVE;

        $exception = new InvalidSubCommandException($command, $subCommand);

        $this->assertEquals(
            'Sub-command '.$subCommand->value.' is not compatible with command '.$command->value,
            $exception->getMessage()
        );
        $this->assertSame($command, $exception->getCommand());
        $this->assertSame($subCommand, $exception->getSubCommand());
        $this->assertEquals($subCommand->value, $exception->getSubCommandText());
    }

    #[Test]
    public function itShouldReturnSubCommandValueFirst(): void
    {
        $command = Command::BBQ;
        $subCommand = SubCommand::LEAVE;
        $subCommandText = 'random-sub-command';

        $exception = new InvalidSubCommandException($command, $subCommand, $subCommandText);

        $this->assertEquals($subCommand->value, $exception->getSubCommandText());
        $this->assertNotEquals($subCommandText, $exception->getSubCommandText());
    }

    #[Test]
    public function itShouldReturnSubCommandTextIfNoSubCommandProvided(): void
    {
        $command = Command::BBQ;
        $subCommandText = 'sub-command-text';

        $exception = new InvalidSubCommandException($command, subCommandText: $subCommandText);

        $this->assertNull($exception->getSubCommand());
        $this->assertEquals($subCommandText, $exception->getSubCommandText());
    }
}

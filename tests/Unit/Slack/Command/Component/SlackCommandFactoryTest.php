<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Command\Component;

use App\Slack\Command\Command;
use App\Slack\Command\Component\Exception\InvalidArgumentCountException;
use App\Slack\Command\Component\Exception\InvalidSubCommandException;
use App\Slack\Command\Component\Exception\SubCommandMissingException;
use App\Slack\Command\Component\SlackCommandFactory;
use App\Slack\Command\SubCommand;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;

#[CoversClass(SlackCommandFactory::class)]
class SlackCommandFactoryTest extends KernelTestCase
{
    #[Test]
    public function itShouldThrowValueErrorIfCommandDoesNotExist(): void
    {
        $request = new Request();

        $request->request->set('command', 'non-existent-command');

        $factory = new SlackCommandFactory();

        $this->expectException(\ValueError::class);

        try {
            $factory->createFromRequest($request);
        } catch (\ValueError $exception) {
            $this->assertStringContainsString('non-existent-command', $exception->getMessage());

            throw $exception;
        }
    }

    #[Test]
    public function itShouldThrowSubCommandMissingExceptionIfCommandExpectsSubCommandButNoneWasGiven(): void
    {
        $request = new Request();

        $request->request->set('command', 'bbq-admin');

        $factory = new SlackCommandFactory();

        $this->expectException(SubCommandMissingException::class);

        try {
            $factory->createFromRequest($request);
        } catch (SubCommandMissingException $exception) {
            $this->assertEquals(Command::BBQ_ADMIN, $exception->getCommand());

            throw $exception;
        }
    }

    #[Test]
    public function itShouldThrowValueErrorIfSubCommandDoesNotExist(): void
    {
        $request = new Request();

        $request->request->set('command', 'bbq-admin');
        $request->request->set('text', 'non-existent-sub-command');

        $factory = new SlackCommandFactory();

        $this->expectException(\ValueError::class);

        try {
            $factory->createFromRequest($request);
        } catch (\ValueError $exception) {
            $this->assertStringContainsString('non-existent-sub-command', $exception->getMessage());

            throw $exception;
        }
    }

    #[Test]
    public function itShouldThrowInvalidSubCommandExceptionIfSubCommandUsedIsNotApplicableToCommand(): void
    {
        $request = new Request();

        $request->request->set('command', 'bbq');
        $request->request->set('text', 'add');

        $factory = new SlackCommandFactory();

        $this->expectException(InvalidSubCommandException::class);

        try {
            $factory->createFromRequest($request);
        } catch (InvalidSubCommandException $exception) {
            $this->assertEquals(Command::BBQ, $exception->getCommand());
            $this->assertEquals(SubCommand::ADD, $exception->getSubCommand());

            throw $exception;
        }
    }

    #[Test]
    public function itShouldThrowInvalidArgumentCountExceptionIfArgumentMissing(): void
    {
        $request = new Request();

        $request->request->set('command', 'bbq');

        $factory = new SlackCommandFactory();

        $this->expectException(InvalidArgumentCountException::class);

        try {
            $factory->createFromRequest($request);
        } catch (InvalidArgumentCountException $exception) {
            $this->assertEquals(Command::BBQ, $exception->getCommand());
            $this->assertEquals(null, $exception->getSubCommand());

            throw $exception;
        }
    }

    #[Test]
    public function itShouldThrowInvalidArgumentCountExceptionIfArgumentMissingWithSubCommand(): void
    {
        $request = new Request();

        $request->request->set('command', 'bbq');
        $request->request->set('text', 'join');

        $factory = new SlackCommandFactory();

        $this->expectException(InvalidArgumentCountException::class);

        try {
            $factory->createFromRequest($request);
        } catch (InvalidArgumentCountException $exception) {
            $this->assertEquals(Command::BBQ, $exception->getCommand());
            $this->assertEquals(SubCommand::JOIN, $exception->getSubCommand());

            throw $exception;
        }
    }

    #[Test]
    public function itShouldReturnSlackCommandIfCommandDoesNotRequireSubCommand(): void
    {
        $request = new Request();

        $request->request->set('command', 'bbq');
        $request->request->set('text', 'queueName');

        $factory = new SlackCommandFactory();

        $command = $factory->createFromRequest($request);

        $this->assertEquals(Command::BBQ, $command->getCommand());
        $this->assertEquals('queueName', $command->getArgument('queue'));
        $this->assertCount(1, $command->getArguments());

        $this->assertArrayHasKey('queue', $command->getArguments());
        $this->assertEquals('queueName', $command->getArguments()['queue']);
    }

    #[Test]
    public function itShouldReturnSlackCommandIfCommandRequiresSubCommandAndArguments(): void
    {
        $request = new Request();

        $request->request->set('command', 'bbq');
        $request->request->set('text', 'join queueName');

        $factory = new SlackCommandFactory();

        $command = $factory->createFromRequest($request);

        $this->assertEquals(Command::BBQ, $command->getCommand());
        $this->assertEquals(SubCommand::JOIN, $command->getSubCommand());
        $this->assertCount(1, $command->getArguments());
        $this->assertArrayHasKey('queue', $command->getArguments());
        $this->assertEquals('queueName', $command->getArguments()['queue']);
        $this->assertEquals('queueName', $command->getArgument('queue'));
    }
}

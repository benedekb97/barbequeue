<?php

declare(strict_types=1);

namespace App\Tests\Unit\Controller\Slack;

use App\Controller\Slack\CommandController;
use App\Message\Slack\SlackCommandMessage;
use App\Slack\Command\Command;
use App\Slack\Command\Exception\InvalidArgumentCountException;
use App\Slack\Command\Exception\InvalidCommandException;
use App\Slack\Command\Exception\InvalidSubCommandException;
use App\Slack\Command\Exception\SubCommandMissingException;
use App\Slack\Command\Factory\SlackCommandFactory;
use App\Slack\Command\SlackCommand;
use App\Slack\Command\SubCommand;
use App\Tests\Unit\LoggerAwareTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

#[CoversClass(CommandController::class)]
class CommandControllerTest extends LoggerAwareTestCase
{
    #[Test, DataProvider('provideForItShouldReturnJsonResponseOkIfExceptionThrown')]
    public function itShouldReturnJsonResponseOkIfExceptionThrown(\Throwable $exception, string $message): void
    {
        $request = new Request();

        $commandFactory = $this->createMock(SlackCommandFactory::class);
        $commandFactory->expects($this->once())
            ->method('createFromRequest')
            ->with($request)
            ->willThrowException($exception);

        $this->expectsDebug($message);

        $messageBus = $this->createMock(MessageBusInterface::class);
        $messageBus->expects($this->never())
            ->method('dispatch');

        $controller = new CommandController(
            $commandFactory,
            $messageBus,
            $this->getLogger(),
        );

        $response = $controller($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
    }

    public static function provideForItShouldReturnJsonResponseOkIfExceptionThrown(): array
    {
        return [
            'SubCommandMissingException' => [
                $exception = new SubCommandMissingException(Command::BBQ),
                $exception->getMessage(),
            ],
            'InvalidArgumentCountException' => [
                $exception = new InvalidArgumentCountException(Command::BBQ, SubCommand::EDIT_QUEUE),
                $exception->getMessage(),
            ],
            'InvalidSubCommandException' => [
                $exception = new InvalidSubCommandException(Command::BBQ, SubCommand::EDIT_QUEUE),
                $exception->getMessage(),
            ],
            'InvalidCommandException' => [
                $exception = new InvalidCommandException('invalid-command'),
                $exception->getMessage(),
            ],
        ];
    }

    #[Test]
    public function itShouldDispatchSlackCommandMessageIfNoExceptionThrownAndReturnPlainResponseOk(): void
    {
        $request = new Request();

        $slackCommand = $this->createMock(SlackCommand::class);
        $slackCommand->expects($this->once())
            ->method('getCommand')
            ->willReturn($command = Command::BBQ);

        $slackCommand->expects($this->once())
            ->method('getSubCommand')
            ->willReturn($subCommand = SubCommand::JOIN);

        $slackCommand->expects($this->once())
            ->method('getArguments')
            ->willReturn([$arguments = 'argument']);

        $commandFactory = $this->createMock(SlackCommandFactory::class);
        $commandFactory->expects($this->once())
            ->method('createFromRequest')
            ->with($request)
            ->willReturn($slackCommand);

        $this->expectsInfo(
            'Dispatching command to asynchronous consumer: {command} {subCommand} {arguments}',
            [
                'command' => $command->value,
                'subCommand' => $subCommand->value,
                'arguments' => $arguments,
            ],
        );

        $messageBus = $this->createMock(MessageBusInterface::class);
        $messageBus->expects($this->once())
            ->method('dispatch')
            ->willReturnCallback(function ($message) use ($slackCommand) {
                $this->assertInstanceOf(SlackCommandMessage::class, $message);
                $this->assertInstanceOf(SlackCommand::class, $message->getCommand());
                $this->assertSame($slackCommand, $message->getCommand());

                return new Envelope($message);
            });

        $controller = new CommandController(
            $commandFactory,
            $messageBus,
            $this->getLogger(),
        );

        $response = $controller($request);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
    }
}

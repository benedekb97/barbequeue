<?php

declare(strict_types=1);

namespace App\Tests\Unit\Controller;

use App\Controller\Slack\CommandController;
use App\Message\SlackCommandMessage;
use App\Slack\Command\Command;
use App\Slack\Command\Component\Exception\InvalidArgumentCountException;
use App\Slack\Command\Component\Exception\InvalidSubCommandException;
use App\Slack\Command\Component\Exception\SubCommandMissingException;
use App\Slack\Command\Component\SlackCommand;
use App\Slack\Command\Component\SlackCommandFactory;
use App\Slack\Command\SubCommand;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

#[CoversClass(CommandController::class)]
class CommandControllerTest extends KernelTestCase
{
    #[Test, DataProvider('provideForItShouldReturnJsonResponseOkIfExceptionThrown')]
    public function itShouldReturnJsonResponseOkIfExceptionThrown(\Throwable $exception, string $message): void
    {
        $request = new Request();

        $commandFactory = $this->createMock(SlackCommandFactory::class);
        $commandFactory->expects(self::once())
            ->method('createFromRequest')
            ->with($request)
            ->willThrowException($exception);

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())
            ->method('debug')
            ->with($message);

        $messageBus = $this->createMock(MessageBusInterface::class);
        $messageBus->expects(self::never())
            ->method('dispatch');

        $controller = new CommandController(
            $commandFactory,
            $messageBus,
            $logger
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
                $exception = new InvalidArgumentCountException(Command::BBQ, SubCommand::QUEUE),
                $exception->getMessage(),
            ],
            'InvalidSubCommandException' => [
                $exception = new InvalidSubCommandException(Command::BBQ, SubCommand::QUEUE),
                $exception->getMessage(),
            ],
            'ValueError' => [
                $exception = new \ValueError('ValueError'),
                $exception->getMessage(),
            ],
        ];
    }

    #[Test]
    public function itShouldDispatchSlackCommandMessageIfNoExceptionThrownAndReturnPlainResponseOk(): void
    {
        $request = new Request();

        $command = $this->createStub(SlackCommand::class);

        $commandFactory = $this->createMock(SlackCommandFactory::class);
        $commandFactory->expects(self::once())
            ->method('createFromRequest')
            ->with($request)
            ->willReturn($command);

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::never())
            ->method('debug');

        $messageBus = $this->createMock(MessageBusInterface::class);
        $messageBus->expects(self::once())
            ->method('dispatch')
            ->willReturnCallback(function ($message) use ($command) {
                $this->assertInstanceOf(SlackCommandMessage::class, $message);
                $this->assertInstanceOf(SlackCommand::class, $message->getCommand());
                $this->assertSame($command, $message->getCommand());

                return new Envelope($message);
            });

        $controller = new CommandController(
            $commandFactory,
            $messageBus,
            $logger
        );

        $response = $controller($request);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
    }
}

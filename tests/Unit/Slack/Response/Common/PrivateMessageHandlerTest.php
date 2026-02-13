<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Response\Common;

use App\Entity\Workspace;
use App\Slack\Client\Exception\UnauthorisedClientException;
use App\Slack\Response\PrivateMessage\Handler\PrivateMessageHandlerInterface;
use App\Slack\Response\PrivateMessage\PrivateMessageHandler;
use App\Slack\Response\PrivateMessage\SlackPrivateMessage;
use JoliCode\Slack\Exception\SlackErrorResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(PrivateMessageHandler::class)]
class PrivateMessageHandlerTest extends KernelTestCase
{
    #[Test]
    public function itShouldLogErrorsIfSlackErrorResponseThrownOnHandle(): void
    {
        $response = $this->createStub(SlackPrivateMessage::class);

        $exception = $this->createMock(SlackErrorResponse::class);
        $exception->expects($this->once())
            ->method('getResponseMetadata')
            ->willReturn('metadata');

        $handler = $this->createMock(PrivateMessageHandlerInterface::class);
        $handler->expects($this->once())
            ->method('supports')
            ->with($response)
            ->willReturn(true);

        $handler->expects($this->once())
            ->method('handle')
            ->with($response)
            ->willThrowException($exception);

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->exactly(2))
            ->method('debug')
            ->withAnyParameters();

        $handler = new PrivateMessageHandler($logger, [$handler]);

        $handler->handle($response);
    }

    #[Test]
    public function itShouldHandlePrivateMessage(): void
    {
        $response = $this->createStub(SlackPrivateMessage::class);

        $handler = $this->createMock(PrivateMessageHandlerInterface::class);
        $handler->expects($this->once())
            ->method('supports')
            ->with($response)
            ->willReturn(true);

        $handler->expects($this->once())
            ->method('handle')
            ->with($response);

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->never())
            ->method('debug')
            ->withAnyParameters();

        $handler = new PrivateMessageHandler($logger, [$handler]);

        $handler->handle($response);
    }

    #[Test]
    public function itShouldLogErrorIfUnauthorisedClientExceptionThrown(): void
    {
        $response = $this->createStub(SlackPrivateMessage::class);

        $workspace = $this->createMock(Workspace::class);
        $workspace->expects($this->once())
            ->method('getName')
            ->willReturn('workspaceName');

        $exception = new UnauthorisedClientException($workspace);

        $handler = $this->createMock(PrivateMessageHandlerInterface::class);
        $handler->expects($this->once())
            ->method('supports')
            ->with($response)
            ->willReturn(true);

        $handler->expects($this->once())
            ->method('handle')
            ->with($response)
            ->willThrowException($exception);

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('error')
            ->with('Could not resolve bot token for workspace workspaceName');

        $handler = new PrivateMessageHandler($logger, [$handler]);

        $handler->handle($response);
    }
}

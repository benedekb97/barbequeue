<?php

declare(strict_types=1);

namespace App\Tests\Unit\MessageHandler\Slack;

use App\Message\Slack\SlackCommandMessage;
use App\MessageHandler\Slack\SlackCommandMessageHandler;
use App\Slack\Command\Handler\SlackCommandHandlerInterface;
use App\Slack\Command\SlackCommand;
use App\Slack\Response\Command\SlackCommandResponse;
use App\Slack\Response\Interaction\InteractionResponseHandler;
use App\Slack\Response\Interaction\SlackInteractionResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(SlackCommandMessageHandler::class)]
class SlackCommandMessageHandlerTest extends KernelTestCase
{
    #[Test]
    public function itShouldSendResponseIfInstanceOfSlackInteractionResponse(): void
    {
        $response = $this->createStub(SlackInteractionResponse::class);

        $command = $this->createMock(SlackCommand::class);
        $command->expects($this->once())
            ->method('getResponse')
            ->willReturn($response);

        $command->expects($this->once())
            ->method('getResponseUrl')
            ->willReturn($responseUrl = 'responseUrl');

        $message = $this->createMock(SlackCommandMessage::class);
        $message->expects($this->once())
            ->method('getCommand')
            ->willReturn($command);

        $responseHandler = $this->createMock(InteractionResponseHandler::class);
        $responseHandler->expects($this->once())
            ->method('handle')
            ->with($responseUrl, $response);

        $handler = new SlackCommandMessageHandler([], $responseHandler);

        $handler($message);
    }

    #[Test]
    public function itShouldNotSendResponseIfInstanceOfSlackCommandResponse(): void
    {
        $response = $this->createStub(SlackCommandResponse::class);

        $command = $this->createMock(SlackCommand::class);
        $command->expects($this->once())
            ->method('getResponse')
            ->willReturn($response);

        $command->expects($this->never())
            ->method('getResponseUrl');

        $message = $this->createMock(SlackCommandMessage::class);
        $message->expects($this->once())
            ->method('getCommand')
            ->willReturn($command);

        $responseHandler = $this->createMock(InteractionResponseHandler::class);
        $responseHandler->expects($this->never())
            ->method('handle')
            ->withAnyParameters();

        $handler = new SlackCommandMessageHandler([], $responseHandler);

        $handler($message);
    }

    #[Test]
    public function itShouldNotSendResponseIfResponseIsNull(): void
    {
        $command = $this->createMock(SlackCommand::class);
        $command->expects($this->once())
            ->method('getResponse')
            ->willReturn(null);

        $command->expects($this->never())
            ->method('getResponseUrl');

        $message = $this->createMock(SlackCommandMessage::class);
        $message->expects($this->once())
            ->method('getCommand')
            ->willReturn($command);

        $responseHandler = $this->createMock(InteractionResponseHandler::class);
        $responseHandler->expects($this->never())
            ->method('handle')
            ->withAnyParameters();

        $handler = new SlackCommandMessageHandler([], $responseHandler);

        $handler($message);
    }

    #[Test]
    public function itShouldContinueHandlingIfCommandIsPending(): void
    {
        $callCounter = 0;

        $command = $this->createMock(SlackCommand::class);
        $command->expects($this->exactly(3))
            ->method('isPending')
            ->willReturnCallback(function () use (&$callCounter) {
                return ++$callCounter < 3;
            });

        $command->expects($this->once())
            ->method('getResponse')
            ->willReturn(null);

        $message = $this->createMock(SlackCommandMessage::class);
        $message->expects($this->once())
            ->method('getCommand')
            ->willReturn($command);

        $firstCommandHandler = $this->createMock(SlackCommandHandlerInterface::class);
        $firstCommandHandler->expects($this->once())
            ->method('supports')
            ->willReturn(false);

        $firstCommandHandler->expects($this->never())
            ->method('handle')
            ->withAnyParameters();

        $secondCommandHandler = $this->createMock(SlackCommandHandlerInterface::class);
        $secondCommandHandler->expects($this->once())
            ->method('supports')
            ->willReturn(true);

        $secondCommandHandler->expects($this->once())
            ->method('handle')
            ->with($command);

        $thirdCommandHandler = $this->createMock(SlackCommandHandlerInterface::class);
        $thirdCommandHandler->expects($this->once())
            ->method('supports')
            ->willReturn(true);

        $thirdCommandHandler->expects($this->once())
            ->method('handle')
            ->with($command);

        $fourthCommandHandler = $this->createMock(SlackCommandHandlerInterface::class);
        $fourthCommandHandler->expects($this->once())
            ->method('supports')
            ->willReturn(true);

        $fourthCommandHandler->expects($this->never())
            ->method('handle')
            ->withAnyParameters();

        $handler = new SlackCommandMessageHandler([
            $firstCommandHandler,
            $secondCommandHandler,
            $thirdCommandHandler,
            $fourthCommandHandler,
        ], $this->createStub(InteractionResponseHandler::class));

        $handler($message);
    }
}

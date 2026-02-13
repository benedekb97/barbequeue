<?php

declare(strict_types=1);

namespace App\Tests\Unit\MessageHandler\Slack;

use App\Message\Slack\SlackInteractionMessage;
use App\MessageHandler\Slack\SlackInteractionMessageHandler;
use App\Slack\Interaction\Handler\SlackInteractionHandlerInterface;
use App\Slack\Interaction\Interaction;
use App\Slack\Interaction\InteractionType;
use App\Slack\Interaction\SlackInteraction;
use App\Slack\Response\Interaction\InteractionResponseHandler;
use App\Slack\Response\Interaction\SlackInteractionResponse;
use App\Slack\Response\PrivateMessage\PrivateMessageHandler;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(SlackInteractionMessageHandler::class)]
class SlackInteractionMessageHandlerTest extends KernelTestCase
{
    #[Test]
    public function itShouldLogAnErrorIfInteractionHasResponseUrlAndInteractionWasNotHandled(): void
    {
        $interaction = $this->createMock(SlackInteraction::class);
        $interaction->expects($this->once())
            ->method('isPending')
            ->willReturn(true);

        $interaction->expects($this->once())
            ->method('getResponseUrl')
            ->willReturn('responseUrl');

        $interaction->expects($this->once())
            ->method('getResponse')
            ->willReturn(null);

        $message = $this->createMock(SlackInteractionMessage::class);
        $message->expects($this->once())
            ->method('getInteraction')
            ->willReturn($interaction);

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('error')
            ->willReturnCallback(function (string $message, array $context): void {
                $this->assertStringContainsString('Unhandled interaction: {interaction} {type}', $message);

                $this->assertArrayHasKey('interaction', $context);
                $this->assertEquals(Interaction::EDIT_QUEUE, $context['interaction']);

                $this->assertArrayHasKey('type', $context);
                $this->assertEquals(InteractionType::VIEW_SUBMISSION, $context['type']);
            });

        $interactionResponseHandler = $this->createMock(InteractionResponseHandler::class);
        $interactionResponseHandler->expects($this->never())
            ->method('handle')
            ->withAnyParameters();

        $privateMessageHandler = $this->createMock(PrivateMessageHandler::class);
        $privateMessageHandler->expects($this->never())
            ->method('handle')
            ->withAnyParameters();

        $interaction->expects($this->once())
            ->method('getInteraction')
            ->willReturn(Interaction::EDIT_QUEUE);

        $interaction->expects($this->once())
            ->method('getType')
            ->willReturn(InteractionType::VIEW_SUBMISSION);

        $handler = new SlackInteractionMessageHandler(
            [],
            $logger,
            $interactionResponseHandler,
        );

        $handler($message);
    }

    #[Test]
    public function itShouldCallInteractionResponseHandlerIfResponseInstanceOfSlackInteractionResponse(): void
    {
        $response = $this->createStub(SlackInteractionResponse::class);

        $interaction = $this->createMock(SlackInteraction::class);
        $interaction->expects($this->once())
            ->method('isPending')
            ->willReturn(false);

        $interaction->expects($this->once())
            ->method('getResponseUrl')
            ->willReturn($responseUrl = 'responseUrl');

        $interaction->expects($this->once())
            ->method('getResponse')
            ->willReturn($response);

        $message = $this->createMock(SlackInteractionMessage::class);
        $message->expects($this->once())
            ->method('getInteraction')
            ->willReturn($interaction);

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->never())
            ->method('error')
            ->withAnyParameters();

        $interactionResponseHandler = $this->createMock(InteractionResponseHandler::class);
        $interactionResponseHandler->expects($this->once())
            ->method('handle')
            ->with($responseUrl, $response);

        $privateMessageHandler = $this->createMock(PrivateMessageHandler::class);
        $privateMessageHandler->expects($this->never())
            ->method('handle')
            ->withAnyParameters();

        $handler = new SlackInteractionMessageHandler(
            [],
            $logger,
            $interactionResponseHandler,
        );

        $handler($message);
    }

    #[Test]
    public function itShouldNotCallSupportsMethodIfInteractionIsNoLongerPending(): void
    {
        $firstHandler = $this->createMock(SlackInteractionHandlerInterface::class);
        $firstHandler->expects($this->never())
            ->method('supports')
            ->withAnyParameters();

        $firstHandler->expects($this->never())
            ->method('handle')
            ->withAnyParameters();

        $interaction = $this->createMock(SlackInteraction::class);
        $interaction->expects($this->exactly(2))
            ->method('isPending')
            ->willReturn(false);

        $interaction->expects($this->once())
            ->method('getResponse')
            ->willReturn(null);

        $message = $this->createMock(SlackInteractionMessage::class);
        $message->expects($this->once())
            ->method('getInteraction')
            ->willReturn($interaction);

        $handler = new SlackInteractionMessageHandler(
            [$firstHandler],
            $this->createStub(LoggerInterface::class),
            $this->createStub(InteractionResponseHandler::class),
        );

        $handler($message);
    }

    #[Test]
    public function itShouldCallHandleOnHandlerIfSupported(): void
    {
        $callCount = 0;

        $interaction = $this->createMock(SlackInteraction::class);
        $interaction->expects($this->exactly(2))
            ->method('isPending')
            ->willReturnCallback(function () use (&$callCount): bool {
                return ++$callCount < 2;
            });

        $interaction->expects($this->once())
            ->method('getResponse')
            ->willReturn(null);

        $firstHandler = $this->createMock(SlackInteractionHandlerInterface::class);
        $firstHandler->expects($this->once())
            ->method('supports')
            ->with($interaction)
            ->willReturn(true);

        $firstHandler->expects($this->once())
            ->method('handle')
            ->with($interaction);

        $message = $this->createMock(SlackInteractionMessage::class);
        $message->expects($this->once())
            ->method('getInteraction')
            ->willReturn($interaction);

        $handler = new SlackInteractionMessageHandler(
            [$firstHandler],
            $this->createStub(LoggerInterface::class),
            $this->createStub(InteractionResponseHandler::class),
        );

        $handler($message);
    }

    #[Test]
    public function itShouldNotCallHandleOnHandlerIfNotSupported(): void
    {
        $callCount = 0;

        $interaction = $this->createMock(SlackInteraction::class);
        $interaction->expects($this->exactly(3))
            ->method('isPending')
            ->willReturnCallback(function () use (&$callCount): bool {
                return ++$callCount < 3;
            });

        $interaction->expects($this->once())
            ->method('getResponse')
            ->willReturn(null);

        $firstHandler = $this->createMock(SlackInteractionHandlerInterface::class);
        $firstHandler->expects($this->once())
            ->method('supports')
            ->with($interaction)
            ->willReturn(false);

        $firstHandler->expects($this->never())
            ->method('handle')
            ->withAnyParameters();

        $secondHandler = $this->createMock(SlackInteractionHandlerInterface::class);
        $secondHandler->expects($this->once())
            ->method('supports')
            ->with($interaction)
            ->willReturn(true);

        $secondHandler->expects($this->once())
            ->method('handle')
            ->with($interaction);

        $message = $this->createMock(SlackInteractionMessage::class);
        $message->expects($this->once())
            ->method('getInteraction')
            ->willReturn($interaction);

        $handler = new SlackInteractionMessageHandler(
            [$firstHandler, $secondHandler],
            $this->createStub(LoggerInterface::class),
            $this->createStub(InteractionResponseHandler::class),
        );

        $handler($message);
    }
}

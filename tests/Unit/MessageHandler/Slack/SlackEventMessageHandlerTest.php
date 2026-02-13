<?php

declare(strict_types=1);

namespace App\Tests\Unit\MessageHandler\Slack;

use App\Message\Slack\SlackEventMessage;
use App\MessageHandler\Slack\SlackEventMessageHandler;
use App\Slack\Event\Component\SlackEventInterface;
use App\Slack\Event\Event;
use App\Slack\Event\Handler\SlackEventHandlerInterface;
use App\Tests\Unit\LoggerAwareTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(SlackEventMessageHandler::class)]
class SlackEventMessageHandlerTest extends LoggerAwareTestCase
{
    #[Test]
    public function itShouldLogWarningIfEventIsNotHandled(): void
    {
        $event = $this->createMock(SlackEventInterface::class);
        $event->expects($this->once())
            ->method('getType')
            ->willReturn($eventType = Event::APP_HOME_OPENED);

        $message = $this->createMock(SlackEventMessage::class);
        $message->expects($this->once())
            ->method('getEvent')
            ->willReturn($event);

        $this->expectsWarning('Unhandled event {type}.', [
            'type' => $eventType->value,
        ]);

        $messageHandler = new SlackEventMessageHandler([], $this->getLogger());

        $messageHandler($message);
    }

    #[Test]
    public function itShouldReturnOnceHandled(): void
    {
        $event = $this->createStub(SlackEventInterface::class);

        $message = $this->createMock(SlackEventMessage::class);
        $message->expects($this->once())
            ->method('getEvent')
            ->willReturn($event);

        $supportedHandler = $this->createMock(SlackEventHandlerInterface::class);
        $supportedHandler->expects($this->once())
            ->method('supports')
            ->with($event)
            ->willReturn(true);

        $supportedHandler->expects($this->once())
            ->method('handle')
            ->with($event);

        $secondSupportedHandler = $this->createMock(SlackEventHandlerInterface::class);
        $secondSupportedHandler->expects($this->never())
            ->method('supports')
            ->withAnyParameters();

        $secondSupportedHandler->expects($this->never())
            ->method('handle')
            ->withAnyParameters();

        $messageHandler = new SlackEventMessageHandler([
            $supportedHandler,
            $secondSupportedHandler,
        ], $this->getLogger());

        $messageHandler($message);
    }
}

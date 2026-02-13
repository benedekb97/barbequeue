<?php

declare(strict_types=1);

namespace App\Tests\Unit\Controller\Slack;

use App\Controller\Slack\EventController;
use App\Message\Slack\SlackEventMessage;
use App\Slack\Event\Component\SlackEventInterface;
use App\Slack\Event\Component\UrlVerificationEvent;
use App\Slack\Event\Event;
use App\Slack\Event\Exception\UnhandledEventException;
use App\Slack\Event\Exception\UnrecognisedEventException;
use App\Slack\Event\Factory\SlackEventFactory;
use App\Tests\Unit\LoggerAwareTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

#[CoversClass(EventController::class)]
class EventControllerTest extends LoggerAwareTestCase
{
    #[Test]
    public function itShouldLogAndReturnEmptyResponseIfUnrecognisedEventExceptionThrown(): void
    {
        $request = $this->createStub(Request::class);

        $exception = $this->createMock(UnrecognisedEventException::class);
        $exception->expects($this->once())
            ->method('getType')
            ->willReturn($eventType = 'eventType');

        $factory = $this->createMock(SlackEventFactory::class);
        $factory->expects($this->once())
            ->method('create')
            ->with($request)
            ->willThrowException($exception);

        $this->expectsDebug('Unrecognised event exception received with {type}.', [
            'type' => $eventType,
        ]);

        $controller = new EventController(
            $factory,
            $this->getLogger(),
            $this->createStub(MessageBusInterface::class),
        );

        $response = $controller($request);

        $this->assertEmpty($response->getContent());
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
    }

    #[Test]
    public function itShouldLogAndReturnEmptyResponseIfUnhandledEventExceptionThrown(): void
    {
        $request = $this->createStub(Request::class);

        $exception = $this->createMock(UnhandledEventException::class);
        $exception->expects($this->once())
            ->method('getEvent')
            ->willReturn($event = Event::URL_VERIFICATION);

        $this->expectsDebug('Unhandled event exception received while trying to parse {event}.', [
            'event' => $event->value,
        ]);

        $factory = $this->createMock(SlackEventFactory::class);
        $factory->expects($this->once())
            ->method('create')
            ->with($request)
            ->willThrowException($exception);

        $controller = new EventController(
            $factory,
            $this->getLogger(),
            $this->createStub(MessageBusInterface::class),
        );

        $response = $controller($request);

        $this->assertEmpty($response->getContent());
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
    }

    #[Test]
    public function itShouldReturnChallengeIfEventIsUrlVerificationEvent(): void
    {
        $request = $this->createStub(Request::class);

        $event = $this->createMock(UrlVerificationEvent::class);
        $event->expects($this->once())
            ->method('getChallenge')
            ->willReturn($challenge = 'challenge');

        $factory = $this->createMock(SlackEventFactory::class);
        $factory->expects($this->once())
            ->method('create')
            ->with($request)
            ->willReturn($event);

        $this
            ->expectsDebug('Event parsed successfully as {class}.', [
                'class' => $event::class,
            ])
            ->expectsDebug('URL Verification event received with {challenge}', [
                'challenge' => $challenge,
            ]);

        $messageBus = $this->createMock(MessageBusInterface::class);
        $messageBus->expects($this->never())
            ->method('dispatch')
            ->withAnyParameters();

        $controller = new EventController(
            $factory,
            $this->getLogger(),
            $messageBus,
        );

        $response = $controller($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertIsString($response->getContent());

        $response = json_decode($response->getContent(), true);

        $this->assertIsArray($response);
        $this->assertArrayHasKey('challenge', $response);
        $this->assertEquals($challenge, $response['challenge']);
    }

    #[Test]
    public function itShouldReturnEmptyResponseIfOtherEvent(): void
    {
        $request = $this->createStub(Request::class);

        $event = $this->createStub(SlackEventInterface::class);

        $factory = $this->createMock(SlackEventFactory::class);
        $factory->expects($this->once())
            ->method('create')
            ->with($request)
            ->willReturn($event);

        $this->expectsDebug('Event parsed successfully as {class}.', [
            'class' => $event::class,
        ]);

        $messageBus = $this->createMock(MessageBusInterface::class);
        $messageBus->expects($this->once())
            ->method('dispatch')
            ->willReturnCallback(function ($message) use ($event) {
                $this->assertInstanceOf(SlackEventMessage::class, $message);
                $this->assertSame($event, $message->getEvent());

                return new Envelope($message);
            });

        $controller = new EventController(
            $factory,
            $this->getLogger(),
            $messageBus,
        );

        $response = $controller($request);

        $this->assertEmpty($response->getContent());
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
    }
}

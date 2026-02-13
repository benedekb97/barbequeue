<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Event\Resolver;

use App\Slack\Event\Event;
use App\Slack\Event\Exception\UnrecognisedEventException;
use App\Slack\Event\Resolver\EventTypeResolver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;

#[CoversClass(EventTypeResolver::class)]
class EventTypeResolverTest extends KernelTestCase
{
    #[Test]
    public function itShouldThrowUnrecognisedEventExceptionIfTypeNotProvided(): void
    {
        $request = new Request();

        $resolver = new EventTypeResolver();

        $this->expectException(UnrecognisedEventException::class);

        try {
            $resolver->resolve($request);
        } catch (UnrecognisedEventException $exception) {
            $this->assertNull($exception->getType());

            throw $exception;
        }
    }

    #[Test]
    public function itShouldThrowUnrecognisedEventExceptionIfUnknownEventTypeProvided(): void
    {
        $request = new Request(request: [
            'type' => $type = 'unknown_event_type',
        ]);

        $resolver = new EventTypeResolver();

        $this->expectException(UnrecognisedEventException::class);

        try {
            $resolver->resolve($request);
        } catch (UnrecognisedEventException $exception) {
            $this->assertEquals($type, $exception->getType());

            throw $exception;
        }
    }

    #[Test]
    public function itShouldReturnEvent(): void
    {
        $request = new Request(request: [
            'type' => ($type = Event::URL_VERIFICATION)->value,
        ]);

        $resolver = new EventTypeResolver();

        $result = $resolver->resolve($request);

        $this->assertEquals($type, $result);
    }

    #[Test]
    public function itShouldThrowUnrecognisedEventExceptionIfTypeKeyNotOnEventArray(): void
    {
        $request = new Request(request: [
            'type' => 'event_callback',
            'event' => [],
        ]);

        $resolver = new EventTypeResolver();

        $this->expectException(UnrecognisedEventException::class);

        $resolver->resolve($request);
    }

    #[Test]
    public function itShouldExtractTypeFromEventArrayIfMainTypeEventCallback(): void
    {
        $request = new Request(request: [
            'type' => 'event_callback',
            'event' => [
                'type' => ($type = Event::APP_HOME_OPENED)->value,
            ],
        ]);

        $resolver = new EventTypeResolver();

        $result = $resolver->resolve($request);

        $this->assertEquals($type, $result);
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Event\Factory;

use App\Slack\Event\Component\SlackEventInterface;
use App\Slack\Event\Event;
use App\Slack\Event\Exception\UnhandledEventException;
use App\Slack\Event\Factory\SlackEventFactory;
use App\Slack\Event\Factory\SlackEventFactoryInterface;
use App\Slack\Event\Resolver\EventTypeResolver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;

#[CoversClass(SlackEventFactory::class)]
class SlackEventFactoryTest extends KernelTestCase
{
    #[Test]
    public function itShouldThrowUnhandledEventExceptionIfNoFactorySupportsEventType(): void
    {
        $factory = $this->createMock(SlackEventFactoryInterface::class);
        $factory->expects($this->once())
            ->method('supports')
            ->with($event = Event::URL_VERIFICATION)
            ->willReturn(false);

        $resolver = $this->createMock(EventTypeResolver::class);
        $resolver->expects($this->once())
            ->method('resolve')
            ->with($request = $this->createStub(Request::class))
            ->willReturn($event);

        $slackEventFactory = new SlackEventFactory([$factory], $resolver);

        $this->expectException(UnhandledEventException::class);

        try {
            $slackEventFactory->create($request);
        } catch (UnhandledEventException $exception) {
            $this->assertSame($event, $exception->getEvent());

            throw $exception;
        }
    }

    #[Test]
    public function itShouldReturnFirstSupportedFactoryResult(): void
    {
        $request = $this->createStub(Request::class);

        $factory = $this->createMock(SlackEventFactoryInterface::class);
        $factory->expects($this->once())
            ->method('supports')
            ->with($event = Event::URL_VERIFICATION)
            ->willReturn(true);

        $factory->expects($this->once())
            ->method('create')
            ->with($request)
            ->willReturn($slackEvent = $this->createStub(SlackEventInterface::class));

        $resolver = $this->createMock(EventTypeResolver::class);
        $resolver->expects($this->once())
            ->method('resolve')
            ->with($request)
            ->willReturn($event);

        $slackEventFactory = new SlackEventFactory([$factory], $resolver);

        $result = $slackEventFactory->create($request);

        $this->assertSame($slackEvent, $result);
    }
}

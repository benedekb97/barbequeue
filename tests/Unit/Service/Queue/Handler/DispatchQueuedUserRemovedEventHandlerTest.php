<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Queue\Handler;

use App\Entity\Queue;
use App\Entity\QueuedUser;
use App\Event\QueuedUser\QueuedUserRemovedEvent;
use App\Service\Queue\Context\ContextType;
use App\Service\Queue\Context\QueueContextInterface;
use App\Service\Queue\Handler\DispatchQueuedUserRemovedEventHandler;
use App\Service\Queue\Pop\PopQueueContext;
use App\Tests\Unit\LoggerAwareTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Psr\EventDispatcher\EventDispatcherInterface;

#[CoversClass(DispatchQueuedUserRemovedEventHandler::class)]
class DispatchQueuedUserRemovedEventHandlerTest extends LoggerAwareTestCase
{
    #[Test]
    public function itShouldSupportPopContextType(): void
    {
        $context = $this->createMock(QueueContextInterface::class);
        $context->expects($this->once())
            ->method('getType')
            ->willReturn(ContextType::POP);

        $handler = new DispatchQueuedUserRemovedEventHandler(
            $this->createStub(EventDispatcherInterface::class),
            $this->getLogger(),
        );

        $this->assertTrue($handler->supports($context));
    }

    #[Test]
    public function itShouldSupportLeaveContextType(): void
    {
        $context = $this->createMock(QueueContextInterface::class);
        $context->expects($this->once())
            ->method('getType')
            ->willReturn(ContextType::LEAVE);

        $handler = new DispatchQueuedUserRemovedEventHandler(
            $this->createStub(EventDispatcherInterface::class),
            $this->getLogger(),
        );

        $this->assertTrue($handler->supports($context));
    }

    #[Test]
    public function itShouldNotSupportEditContextType(): void
    {
        $context = $this->createMock(QueueContextInterface::class);
        $context->expects($this->once())
            ->method('getType')
            ->willReturn(ContextType::EDIT);

        $handler = new DispatchQueuedUserRemovedEventHandler(
            $this->createStub(EventDispatcherInterface::class),
            $this->getLogger(),
        );

        $this->assertFalse($handler->supports($context));
    }

    #[Test]
    public function itShouldReturnEarlyIfNotLeaveQueueContextOrPopQueueContext(): void
    {
        $this->expectNotToPerformAssertions();

        $context = $this->createStub(QueueContextInterface::class);

        $handler = new DispatchQueuedUserRemovedEventHandler(
            $this->createStub(EventDispatcherInterface::class),
            $this->getLogger(),
        );

        $handler->handle($context);
    }

    #[Test]
    public function itShouldDispatchQueuedUserRemovedEvent(): void
    {
        $context = $this->createMock(PopQueueContext::class);
        $context->expects($this->once())
            ->method('getType')
            ->willReturn(ContextType::POP);

        $context->expects($this->once())
            ->method('getId')
            ->willReturn($contextId = 'contextId');

        $queue = $this->createMock(Queue::class);
        $queue->expects($this->once())
            ->method('getId')
            ->willReturn($queueId = 1);

        $context->expects($this->exactly(2))
            ->method('getQueue')
            ->willReturn($queue);

        $context->expects($this->once())
            ->method('getQueuedUser')
            ->willReturn($queuedUser = $this->createStub(QueuedUser::class));

        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->willReturnCallback(function ($event) use ($queuedUser, $queue) {
                $this->assertInstanceOf(QueuedUserRemovedEvent::class, $event);
                $this->assertSame($queuedUser, $event->getQueuedUser());
                $this->assertSame($queue, $event->getQueue());
                $this->assertTrue($event->isNotificationRequired());
            });

        $this->expectsDebug('Dispatching queued user removed event for {queue} {contextId} {contextType}', [
            'queue' => $queueId,
            'contextId' => $contextId,
            'contextType' => ContextType::POP->value,
        ]);

        $handler = new DispatchQueuedUserRemovedEventHandler($eventDispatcher, $this->getLogger());

        $handler->handle($context);
    }
}

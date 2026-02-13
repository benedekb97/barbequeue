<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Queue\Join\Handler;

use App\Entity\QueuedUser;
use App\Event\QueuedUser\QueuedUserCreatedEvent;
use App\Service\Queue\Context\ContextType;
use App\Service\Queue\Context\QueueContextInterface;
use App\Service\Queue\Join\Handler\DispatchQueuedUserCreatedEventHandler;
use App\Service\Queue\Join\JoinQueueContext;
use App\Tests\Unit\LoggerAwareTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;

#[CoversClass(DispatchQueuedUserCreatedEventHandler::class)]
class DispatchQueuedUserCreatedEventHandlerTest extends LoggerAwareTestCase
{
    #[Test]
    public function itShouldSupportJoinQueueContext(): void
    {
        $handler = new DispatchQueuedUserCreatedEventHandler(
            $this->createStub(EventDispatcherInterface::class),
            $this->createStub(LoggerInterface::class),
        );

        $this->assertTrue($handler->supports($this->createStub(JoinQueueContext::class)));
    }

    #[Test]
    public function itShouldNotSupportGenericQueueContext(): void
    {
        $handler = new DispatchQueuedUserCreatedEventHandler(
            $this->createStub(EventDispatcherInterface::class),
            $this->createStub(LoggerInterface::class),
        );

        $this->assertFalse($handler->supports($this->createStub(QueueContextInterface::class)));
    }

    #[Test]
    public function itShouldReturnEarlyIfNotJoinQueueContext(): void
    {
        $this->expectNotToPerformAssertions();

        $handler = new DispatchQueuedUserCreatedEventHandler(
            $this->createStub(EventDispatcherInterface::class),
            $this->createStub(LoggerInterface::class),
        );

        $handler->handle($this->createStub(QueueContextInterface::class));
    }

    #[Test]
    public function itShouldDispatchQueuedUserCreatedEvent(): void
    {
        $context = $this->createMock(JoinQueueContext::class);
        $context->expects($this->once())
            ->method('getId')
            ->willReturn($contextId = 'contextId');

        $context->expects($this->once())
            ->method('getType')
            ->willReturn($type = ContextType::JOIN);

        $context->expects($this->once())
            ->method('getQueuedUser')
            ->willReturn($queuedUser = $this->createStub(QueuedUser::class));

        $this->expectsDebug('Dispatching queued user created event for {contextId} {contextType}', [
            'contextId' => $contextId,
            'contextType' => $type->value,
        ]);

        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->willReturnCallback(function ($event) use ($queuedUser) {
                $this->assertInstanceOf(QueuedUserCreatedEvent::class, $event);
                $this->assertSame($queuedUser, $event->getQueuedUser());
            });

        $handler = new DispatchQueuedUserCreatedEventHandler(
            $eventDispatcher,
            $this->getLogger(),
        );

        $handler->handle($context);
    }
}

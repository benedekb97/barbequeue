<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Queue\Handler;

use App\Event\HomeTabUpdatedEvent;
use App\Service\Queue\Context\ContextType;
use App\Service\Queue\Context\QueueContextInterface;
use App\Service\Queue\Edit\EditQueueContext;
use App\Service\Queue\Handler\DispatchHomeTabUpdatedEventHandler;
use App\Service\Queue\Pop\PopQueueContext;
use App\Tests\Unit\LoggerAwareTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Psr\EventDispatcher\EventDispatcherInterface;

#[CoversClass(DispatchHomeTabUpdatedEventHandler::class)]
class DispatchHomeTabUpdatedEventHandlerTest extends LoggerAwareTestCase
{
    #[Test]
    public function itShouldSupportEditQueueContext(): void
    {
        $context = $this->createStub(EditQueueContext::class);

        $handler = new DispatchHomeTabUpdatedEventHandler(
            $this->getLogger(),
            $this->createStub(EventDispatcherInterface::class),
        );

        $this->assertTrue($handler->supports($context));
    }

    #[Test]
    public function itShouldSupportPopQueueContext(): void
    {
        $context = $this->createStub(PopQueueContext::class);

        $handler = new DispatchHomeTabUpdatedEventHandler(
            $this->getLogger(),
            $this->createStub(EventDispatcherInterface::class),
        );

        $this->assertTrue($handler->supports($context));
    }

    #[Test]
    public function itShouldNotSupportGenericContext(): void
    {
        $context = $this->createStub(QueueContextInterface::class);

        $handler = new DispatchHomeTabUpdatedEventHandler(
            $this->getLogger(),
            $this->createStub(EventDispatcherInterface::class),
        );

        $this->assertFalse($handler->supports($context));
    }

    #[Test]
    public function itShouldDispatchHomeTabUpdatedEvent(): void
    {
        $context = $this->createMock(EditQueueContext::class);
        $context->expects($this->once())
            ->method('getId')
            ->willReturn($contextId = 'contextId');

        $context->expects($this->once())
            ->method('getType')
            ->willReturn($type = ContextType::EDIT);

        $this->expectsDebug('Dispatching home tab updated event for {contextId} {contextType}', [
            'contextId' => $contextId,
            'contextType' => $type,
        ]);

        $context->expects($this->once())
            ->method('getUserId')
            ->willReturn($userId = 'userId');

        $context->expects($this->once())
            ->method('getTeamId')
            ->willReturn($teamId = 'teamId');

        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->willReturnCallback(function ($event) use ($userId, $teamId) {
                $this->assertInstanceOf(HomeTabUpdatedEvent::class, $event);

                $this->assertSame($userId, $event->getUserId());
                $this->assertSame($teamId, $event->getTeamId());
            });

        $handler = new DispatchHomeTabUpdatedEventHandler(
            $this->getLogger(),
            $eventDispatcher,
        );

        $handler->handle($context);
    }
}

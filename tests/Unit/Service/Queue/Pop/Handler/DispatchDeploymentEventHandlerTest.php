<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Queue\Pop\Handler;

use App\Entity\Deployment;
use App\Entity\Queue;
use App\Entity\QueuedUser;
use App\Entity\Repository;
use App\Entity\Workspace;
use App\Event\Deployment\DeploymentCancelledEvent;
use App\Event\Deployment\DeploymentCompletedEvent;
use App\Service\Queue\Context\ContextType;
use App\Service\Queue\Context\QueueContextInterface;
use App\Service\Queue\Pop\Handler\DispatchDeploymentEventHandler;
use App\Service\Queue\Pop\PopQueueContext;
use App\Tests\Unit\LoggerAwareTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Psr\EventDispatcher\EventDispatcherInterface;

#[CoversClass(DispatchDeploymentEventHandler::class)]
class DispatchDeploymentEventHandlerTest extends LoggerAwareTestCase
{
    #[Test]
    public function itShouldSupportJoinContextWithDeployment(): void
    {
        $context = $this->createMock(PopQueueContext::class);
        $context->expects($this->once())
            ->method('getQueuedUser')
            ->willReturn($this->createStub(Deployment::class));

        $handler = new DispatchDeploymentEventHandler(
            $this->getLogger(),
            $this->createStub(EventDispatcherInterface::class),
        );

        $this->assertTrue($handler->supports($context));
    }

    #[Test]
    public function itShouldNotSupportPopQueueContextWithQueuedUser(): void
    {
        $context = $this->createMock(PopQueueContext::class);
        $context->expects($this->once())
            ->method('getQueuedUser')
            ->willReturn($this->createStub(QueuedUser::class));

        $handler = new DispatchDeploymentEventHandler(
            $this->getLogger(),
            $this->createStub(EventDispatcherInterface::class),
        );

        $this->assertFalse($handler->supports($context));
    }

    #[Test]
    public function itShouldNotSupportGenericQueueContext(): void
    {
        $handler = new DispatchDeploymentEventHandler(
            $this->getLogger(),
            $this->createStub(EventDispatcherInterface::class),
        );

        $this->assertFalse($handler->supports($this->createStub(QueueContextInterface::class)));
    }

    #[Test]
    public function itShouldReturnEarlyIfContextNotPopQueueContext(): void
    {
        $this->expectNotToPerformAssertions();

        $handler = new DispatchDeploymentEventHandler(
            $this->getLogger(),
            $this->createStub(EventDispatcherInterface::class),
        );

        $handler->handle($this->createStub(QueueContextInterface::class));
    }

    #[Test]
    public function itShouldReturnEarlyIfQueuedUserNotDeployment(): void
    {
        $context = $this->createMock(PopQueueContext::class);
        $context->expects($this->once())
            ->method('getQueuedUser')
            ->willReturn($this->createStub(QueuedUser::class));

        $handler = new DispatchDeploymentEventHandler(
            $this->getLogger(),
            $this->createStub(EventDispatcherInterface::class),
        );

        $handler->handle($context);
    }

    #[Test]
    public function itShouldDispatchDeploymentCompletedEventIfDeploymentIsActive(): void
    {
        $context = $this->createMock(PopQueueContext::class);
        $context->expects($this->once())
            ->method('getQueuedUser')
            ->willReturn($deployment = $this->createMock(Deployment::class));

        $deployment->expects($this->once())
            ->method('isActive')
            ->willReturn(true);

        $context->expects($this->once())
            ->method('getWorkspace')
            ->willReturn($workspace = $this->createStub(Workspace::class));

        $context->expects($this->once())
            ->method('getQueue')
            ->willReturn($queue = $this->createMock(Queue::class));

        $queue->expects($this->once())
            ->method('getId')
            ->willReturn($queueId = 1);

        $context->expects($this->once())
            ->method('getId')
            ->willReturn($contextId = 'contextId');

        $context->expects($this->once())
            ->method('getType')
            ->willReturn($contextType = ContextType::JOIN);

        $context->expects($this->once())
            ->method('getRepository')
            ->willReturn($repository = $this->createStub(Repository::class));

        $this->expectsDebug('Dispatching {event} for removing deployment on {queue} for {contextId} {contextType}', [
            'event' => DeploymentCompletedEvent::class,
            'queue' => $queueId,
            'contextId' => $contextId,
            'contextType' => $contextType,
        ]);

        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->willReturnCallback(function ($event) use ($deployment, $workspace, $repository) {
                $this->assertInstanceOf(DeploymentCompletedEvent::class, $event);
                $this->assertSame($deployment, $event->getDeployment());
                $this->assertSame($workspace, $event->getWorkspace());
                $this->assertSame($repository, $event->getRepository());
                $this->assertTrue($event->shouldNotifyOwner());
            });

        $handler = new DispatchDeploymentEventHandler($this->getLogger(), $eventDispatcher);

        $handler->handle($context);
    }

    #[Test]
    public function itShouldDispatchDeploymentCancelledEventIfDeploymentIsNotActive(): void
    {
        $context = $this->createMock(PopQueueContext::class);
        $context->expects($this->once())
            ->method('getQueuedUser')
            ->willReturn($deployment = $this->createMock(Deployment::class));

        $deployment->expects($this->once())
            ->method('isActive')
            ->willReturn(false);

        $context->expects($this->once())
            ->method('getWorkspace')
            ->willReturn($workspace = $this->createStub(Workspace::class));

        $context->expects($this->once())
            ->method('getQueue')
            ->willReturn($queue = $this->createMock(Queue::class));

        $queue->expects($this->once())
            ->method('getId')
            ->willReturn($queueId = 1);

        $context->expects($this->once())
            ->method('getId')
            ->willReturn($contextId = 'contextId');

        $context->expects($this->once())
            ->method('getType')
            ->willReturn($contextType = ContextType::JOIN);

        $context->expects($this->once())
            ->method('getRepository')
            ->willReturn($repository = $this->createStub(Repository::class));

        $this->expectsDebug('Dispatching {event} for removing deployment on {queue} for {contextId} {contextType}', [
            'event' => DeploymentCancelledEvent::class,
            'queue' => $queueId,
            'contextId' => $contextId,
            'contextType' => $contextType,
        ]);

        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->willReturnCallback(function ($event) use ($deployment, $workspace, $repository) {
                $this->assertInstanceOf(DeploymentCancelledEvent::class, $event);
                $this->assertSame($deployment, $event->getDeployment());
                $this->assertSame($workspace, $event->getWorkspace());
                $this->assertSame($repository, $event->getRepository());
                $this->assertTrue($event->shouldNotifyOwner());
            });

        $handler = new DispatchDeploymentEventHandler($this->getLogger(), $eventDispatcher);

        $handler->handle($context);
    }
}

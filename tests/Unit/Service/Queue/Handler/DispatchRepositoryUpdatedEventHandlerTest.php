<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Queue\Handler;

use App\Entity\Deployment;
use App\Entity\QueuedUser;
use App\Entity\Repository;
use App\Entity\Workspace;
use App\Event\Repository\RepositoryUpdatedEvent;
use App\Service\Queue\Context\ContextType;
use App\Service\Queue\Context\QueueContextInterface;
use App\Service\Queue\Handler\DispatchRepositoryUpdatedEventHandler;
use App\Service\Queue\Leave\LeaveQueueContext;
use App\Service\Queue\Pop\PopQueueContext;
use App\Tests\Unit\LoggerAwareTestCase;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Psr\EventDispatcher\EventDispatcherInterface;

#[CoversClass(DispatchRepositoryUpdatedEventHandler::class)]
class DispatchRepositoryUpdatedEventHandlerTest extends LoggerAwareTestCase
{
    #[Test]
    public function itShouldSupportPopQueueContextWithDeployment(): void
    {
        $context = $this->createMock(PopQueueContext::class);
        $context->expects($this->once())
            ->method('getQueuedUser')
            ->willReturn($this->createStub(Deployment::class));

        $handler = new DispatchRepositoryUpdatedEventHandler(
            $this->createStub(EventDispatcherInterface::class),
            $this->getLogger(),
        );

        $this->assertTrue($handler->supports($context));
    }

    #[Test]
    public function itShouldSupportLeaveQueueContextWithDeployment(): void
    {
        $context = $this->createMock(LeaveQueueContext::class);
        $context->expects($this->once())
            ->method('getQueuedUser')
            ->willReturn($this->createStub(Deployment::class));

        $handler = new DispatchRepositoryUpdatedEventHandler(
            $this->createStub(EventDispatcherInterface::class),
            $this->getLogger(),
        );

        $this->assertTrue($handler->supports($context));
    }

    #[Test]
    public function itShouldNotSupportGenericContextWithDeployment(): void
    {
        $context = $this->createStub(QueueContextInterface::class);

        $handler = new DispatchRepositoryUpdatedEventHandler(
            $this->createStub(EventDispatcherInterface::class),
            $this->getLogger(),
        );

        $this->assertFalse($handler->supports($context));
    }

    #[Test]
    public function itShouldNotSupportPopQueueContextWithQueuedUser(): void
    {
        $context = $this->createMock(PopQueueContext::class);
        $context->expects($this->once())
            ->method('getQueuedUser')
            ->willReturn($this->createStub(QueuedUser::class));

        $handler = new DispatchRepositoryUpdatedEventHandler(
            $this->createStub(EventDispatcherInterface::class),
            $this->getLogger(),
        );

        $this->assertFalse($handler->supports($context));
    }

    #[Test]
    public function itShouldNotSupportLeaveQueueContextWithQueuedUser(): void
    {
        $context = $this->createMock(LeaveQueueContext::class);
        $context->expects($this->once())
            ->method('getQueuedUser')
            ->willReturn($this->createStub(QueuedUser::class));

        $handler = new DispatchRepositoryUpdatedEventHandler(
            $this->createStub(EventDispatcherInterface::class),
            $this->getLogger(),
        );

        $this->assertFalse($handler->supports($context));
    }

    #[Test]
    public function itShouldReturnEarlyIfNotLeaveOrPopQueueContext(): void
    {
        $this->expectNotToPerformAssertions();

        $context = $this->createStub(QueueContextInterface::class);

        $handler = new DispatchRepositoryUpdatedEventHandler(
            $this->createStub(EventDispatcherInterface::class),
            $this->getLogger(),
        );

        $handler->handle($context);
    }

    #[Test]
    public function itShouldDispatchEventsForRepositoriesOnWorkspace(): void
    {
        $this->expectsDebug(
            'Dispatching repository updated event for {repository} {contextId} {contextType}',
            [
                'repository' => $repositoryId = 1,
                'contextId' => $contextId = 'contextId',
                'contextType' => ($contextType = ContextType::LEAVE)->value,
            ]
        );

        $repository = $this->createMock(Repository::class);
        $repository->expects($this->once())
            ->method('getId')
            ->willReturn($repositoryId);

        $repository->expects($this->once())
            ->method('isBlockedByDeployment')
            ->willReturN(false);

        $context = $this->createMock(LeaveQueueContext::class);
        $context->expects($this->once())
            ->method('getId')
            ->willReturn($contextId);

        $context->expects($this->once())
            ->method('getWorkspace')
            ->willReturn($workspace = $this->createMock(Workspace::class));

        $context->expects($this->once())
            ->method('getType')
            ->willReturn($contextType);

        $workspace->expects($this->once())
            ->method('getRepositories')
            ->willReturn(new ArrayCollection([$repository, $blockedRepository = $this->createMock(Repository::class)]));

        $blockedRepository->expects($this->once())
            ->method('isBlockedByDeployment')
            ->willReturn(true);

        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->willReturnCallback(function ($event) use ($repository) {
                $this->assertInstanceOf(RepositoryUpdatedEvent::class, $event);
                $this->assertSame($repository, $event->getRepository());
            });

        $handler = new DispatchRepositoryUpdatedEventHandler(
            $eventDispatcher,
            $this->getLogger(),
        );

        $handler->handle($context);
    }
}

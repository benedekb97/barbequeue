<?php

declare(strict_types=1);

namespace App\Tests\Unit\MessageHandler\Queue;

use App\Entity\Deployment;
use App\Entity\Queue;
use App\Entity\QueuedUser;
use App\Entity\Repository;
use App\Entity\Workspace;
use App\Event\Deployment\DeploymentCompletedEvent;
use App\Event\QueuedUser\QueuedUserRemovedEvent;
use App\Event\Repository\RepositoryUpdatedEvent;
use App\Message\Queue\PopQueuesMessage;
use App\MessageHandler\Queue\PopQueuesMessageHandler;
use App\Repository\QueuedUserRepositoryInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(PopQueuesMessageHandler::class)]
class PopQueuesMessageHandlerTest extends KernelTestCase
{
    #[Test]
    public function itShouldDispatchNoEventsIfNoExpiredUsersFound(): void
    {
        $repository = $this->createMock(QueuedUserRepositoryInterface::class);
        $repository->expects($this->once())
            ->method('findAllExpired')
            ->willReturn([]);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->never())
            ->method('remove')
            ->withAnyParameters();

        $entityManager->expects($this->once())
            ->method('flush');

        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects($this->never())
            ->method('dispatch')
            ->withAnyParameters();

        $handler = new PopQueuesMessageHandler($repository, $entityManager, $eventDispatcher);

        $handler($this->createStub(PopQueuesMessage::class));
    }

    /**
     * This test case is also not very likely, as queued_users without a queue do not make sense, however the database
     * field is nullable so a test makes sense.
     */
    #[Test]
    public function itShouldNotDispatchEventIfNoQueueOnUser(): void
    {
        $queuedUser = $this->createMock(QueuedUser::class);
        $queuedUser->expects($this->once())
            ->method('getQueue')
            ->willReturn(null);

        $repository = $this->createMock(QueuedUserRepositoryInterface::class);
        $repository->expects($this->once())
            ->method('findAllExpired')
            ->willReturn([$queuedUser]);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->once())
            ->method('remove')
            ->with($queuedUser);

        $entityManager->expects($this->once())
            ->method('flush');

        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects($this->never())
            ->method('dispatch')
            ->withAnyParameters();

        $handler = new PopQueuesMessageHandler($repository, $entityManager, $eventDispatcher);

        $handler($this->createStub(PopQueuesMessage::class));
    }

    #[Test]
    public function itShouldDispatchEventIfQueuedUserHasQueue(): void
    {
        $queue = $this->createStub(Queue::class);

        $queuedUser = $this->createMock(QueuedUser::class);
        $queuedUser->expects($this->once())
            ->method('getQueue')
            ->willReturn($queue);

        $repository = $this->createMock(QueuedUserRepositoryInterface::class);
        $repository->expects($this->once())
            ->method('findAllExpired')
            ->willReturn([$queuedUser]);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->once())
            ->method('remove')
            ->with($queuedUser);

        $entityManager->expects($this->once())
            ->method('flush');

        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->willReturnCallback(function ($argument) use ($queuedUser, $queue) {
                $this->assertInstanceOf(QueuedUserRemovedEvent::class, $argument);
                $this->assertSame($queuedUser, $argument->getQueuedUser());
                $this->assertSame($queue, $argument->getQueue());
                $this->assertTrue($argument->isNotificationRequired());
                $this->assertTrue($argument->isAutomatic());
            });

        $handler = new PopQueuesMessageHandler($repository, $entityManager, $eventDispatcher);

        $handler($this->createStub(PopQueuesMessage::class));
    }

    #[Test]
    public function itShouldDispatchEventIfQueuedUserIsDeployment(): void
    {
        $queue = $this->createMock(Queue::class);
        $queue->expects($this->once())
            ->method('getWorkspace')
            ->willReturn($workspace = $this->createMock(Workspace::class));

        $queuedUser = $this->createMock(Deployment::class);
        $queuedUser->expects($this->once())
            ->method('getQueue')
            ->willReturn($queue);

        $queue->expects($this->once())
            ->method('removeQueuedUser')
            ->with($queuedUser);

        $repository = $this->createMock(Repository::class);
        $repository->expects($this->once())
            ->method('removeDeployment')
            ->with($queuedUser)
            ->willReturnSelf();

        $repository->expects($this->once())
            ->method('isBlockedByDeployment')
            ->willReturn(false);

        $queuedUser->expects($this->once())
            ->method('getRepository')
            ->willReturn($repository);

        $workspace->expects($this->once())
            ->method('getRepositories')
            ->willReturn(new ArrayCollection([$repository, $blockedRepository = $this->createMock(Repository::class)]));

        $blockedRepository->expects($this->once())
            ->method('isBlockedByDeployment')
            ->willReturn(true);

        $queuedUserRepository = $this->createMock(QueuedUserRepositoryInterface::class);
        $queuedUserRepository->expects($this->once())
            ->method('findAllExpired')
            ->willReturn([$queuedUser]);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->once())
            ->method('remove')
            ->with($queuedUser);

        $entityManager->expects($this->once())
            ->method('flush');

        $callCount = 0;

        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects($this->exactly(3))
            ->method('dispatch')
            ->willReturnCallback(function ($argument) use ($queuedUser, $queue, &$callCount, $repository, $workspace) {
                if (1 === ++$callCount) {
                    $this->assertInstanceOf(QueuedUserRemovedEvent::class, $argument);
                    $this->assertSame($queuedUser, $argument->getQueuedUser());
                    $this->assertSame($queue, $argument->getQueue());
                    $this->assertTrue($argument->isNotificationRequired());
                    $this->assertTrue($argument->isAutomatic());
                }

                if (2 === $callCount) {
                    $this->assertInstanceOf(DeploymentCompletedEvent::class, $argument);
                    $this->assertSame($queuedUser, $argument->getDeployment());
                    $this->assertSame($repository, $argument->getRepository());
                    $this->assertSame($workspace, $argument->getWorkspace());
                    $this->assertTrue($argument->shouldNotifyOwner());
                }

                if (3 === $callCount) {
                    $this->assertInstanceOf(RepositoryUpdatedEvent::class, $argument);
                    $this->assertSame($repository, $argument->getRepository());
                }
            });

        $handler = new PopQueuesMessageHandler($queuedUserRepository, $entityManager, $eventDispatcher);

        $handler($this->createStub(PopQueuesMessage::class));
    }
}

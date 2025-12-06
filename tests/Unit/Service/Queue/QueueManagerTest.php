<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Queue;

use App\Entity\Queue;
use App\Entity\QueuedUser;
use App\Repository\QueueRepositoryInterface;
use App\Service\Queue\Exception\QueueNotFoundException;
use App\Service\Queue\Exception\UnableToJoinQueueException;
use App\Service\Queue\QueueManager;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(QueueManager::class)]
class QueueManagerTest extends KernelTestCase
{
    #[Test]
    public function itShouldThrowQueueNotFoundExceptionIfQueueDoesNotExist(): void
    {
        $repository = $this->createMock(QueueRepositoryInterface::class);
        $repository->expects(self::once())
            ->method('findOneByNameAndDomain')
            ->with($queueName = 'test', $domain = 'test')
            ->willReturn(null);

        $manager = new QueueManager(
            $repository,
            $this->createStub(EntityManagerInterface::class),
        );

        $this->expectException(QueueNotFoundException::class);

        $userId = 'userId';

        try {
            $manager->joinQueue($queueName, $domain, $userId);
        } catch (QueueNotFoundException $e) {
            $this->assertEquals($queueName, $e->getQueueName());
            $this->assertEquals($domain, $e->getDomain());
            $this->assertEquals($userId, $e->getUserId());

            throw $e;
        }
    }

    #[Test]
    public function itShouldThrowUnableToJoinExceptionIfUserCannotJoinQueue(): void
    {
        $queue = $this->createMock(Queue::class);
        $queue->expects(self::once())
            ->method('canJoin')
            ->with($userId = 'userId')
            ->wilLReturn(false);

        $repository = $this->createMock(QueueRepositoryInterface::class);
        $repository->expects(self::once())
            ->method('findOneByNameAndDomain')
            ->with($queueName = 'queueName', $domain = 'domain')
            ->wilLReturn($queue);

        $manager = new QueueManager(
            $repository,
            $this->createStub(EntityManagerInterface::class),
        );

        $this->expectException(UnableToJoinQueueException::class);

        try {
            $manager->joinQueue($queueName, $domain, $userId);
        } catch (UnableToJoinQueueException $e) {
            $this->assertEquals($queue, $e->getQueue());

            throw $e;
        }
    }

    #[Test]
    public function itShouldPersistNewQueuedUserAndReturnTheQueue(): void
    {
        $queue = $this->createMock(Queue::class);
        $queue->expects(self::once())
            ->method('canJoin')
            ->with($userId = 'userId')
            ->wilLReturn(true);

        $entityManager = $this->createMock(EntityManagerInterface::class);

        $queue->expects(self::once())
            ->method('addQueuedUser')
            ->willReturnCallback(function ($argument) use ($entityManager, $queue) {
                $this->assertInstanceOf(QueuedUser::class, $argument);

                $entityManager->expects(self::once())
                    ->method('persist')
                    ->willReturnCallback(function ($persistArgument) use ($argument) {
                        $this->assertSame($argument, $persistArgument);
                    });

                return $queue;
            });

        $repository = $this->createMock(QueueRepositoryInterface::class);
        $repository->expects(self::once())
            ->method('findOneByNameAndDomain')
            ->with($queueName = 'queueName', $domain = 'domain')
            ->willReturn($queue);

        $entityManager->expects(self::once())
            ->method('flush');

        $manager = new QueueManager($repository, $entityManager);

        $result = $manager->joinQueue($queueName, $domain, $userId);

        $this->assertSame($queue, $result);
    }
}

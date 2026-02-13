<?php

declare(strict_types=1);

namespace App\Tests\Unit\DataFixtures\QueuedUser;

use App\DataFixtures\Queue\Queues;
use App\DataFixtures\QueuedUser\QueuedUserFixtures;
use App\Entity\Queue;
use App\Entity\QueuedUser;
use App\Entity\User;
use App\Entity\Workspace;
use App\Repository\QueueRepositoryInterface;
use Carbon\CarbonImmutable;
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(QueuedUserFixtures::class)]
class QueuedUserFixturesTest extends KernelTestCase
{
    #[Test]
    public function itShouldCreateThreeQueuedUsersForEachQueue(): void
    {
        $workspace = $this->createMock(Workspace::class);
        $workspace->expects($this->exactly(2))
            ->method('hasUserWithId')
            ->withAnyParameters()
            ->willReturn(false);

        $workspace->expects($this->exactly(2))
            ->method('addUser')
            ->withAnyParameters()
            ->willReturnSelf();

        $queue = $this->createMock(Queue::class);
        $queue->expects($this->exactly(4))
            ->method('getMaximumEntriesPerUser')
            ->willReturn(null);

        $queue->expects($this->once())
            ->method('getExpiryMinutes')
            ->willReturn(5);

        $queue->expects($this->once())
            ->method('getName')
            ->willReturn(Queues::FIFTEEN_MINUTE_EXPIRY_NO_USER_LIMIT->value);

        $queue->expects($this->exactly(8))
            ->method('getWorkspace')
            ->willReturn($workspace);

        $repository = $this->createMock(QueueRepositoryInterface::class);
        $repository->expects($this->once())
            ->method('findAll')
            ->willReturn([$queue]);

        $objectManager = $this->createMock(ObjectManager::class);
        $objectManager->expects($this->once())
            ->method('getRepository')
            ->with(Queue::class)
            ->willReturn($repository);

        $callCount = 0;

        $objectManager->expects($this->exactly(6))
            ->method('persist')
            ->willReturnCallback(function ($argument) use (&$callCount, $queue, $workspace) {
                if (1 === ++$callCount) {
                    $this->assertInstanceOf(User::class, $argument);

                    $workspace->expects($this->exactly(4))
                        ->method('getUserById')
                        ->withAnyParameters()
                        ->willReturn($argument);

                    $this->assertEquals('userId', $argument->getSlackId());

                    return;
                }

                if (2 === $callCount) {
                    $this->assertInstanceOf(User::class, $argument);

                    $this->assertEquals('expiredUserId', $argument->getSlackId());

                    return;
                }

                $this->assertInstanceOf(QueuedUser::class, $argument);

                if (3 === $callCount) {
                    $this->assertInstanceOf(CarbonImmutable::class, $expiresAt = $argument->getExpiresAt());
                    $this->assertTrue($expiresAt->subYear()->addSecond()->isFuture());
                    $this->assertTrue($expiresAt->subYear()->isNowOrPast());
                }

                $this->assertEquals($queue, $argument->getQueue());

                if (6 === $callCount) {
                    $this->assertInstanceOf(CarbonImmutable::class, $expiresAt = $argument->getExpiresAt());
                    $this->assertTrue($expiresAt->addYear()->subSecond()->isPast());
                    $this->assertTrue($expiresAt->addYear()->addSecond()->isNowOrFuture());
                }
            });

        $objectManager->expects($this->once())
            ->method('flush');

        $fixtures = new QueuedUserFixtures();
        $fixtures->load($objectManager);
    }
}

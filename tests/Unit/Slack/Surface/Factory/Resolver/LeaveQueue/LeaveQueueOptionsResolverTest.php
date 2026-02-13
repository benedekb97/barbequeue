<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Surface\Factory\Resolver\LeaveQueue;

use App\Entity\Queue;
use App\Entity\QueuedUser;
use App\Entity\User;
use App\Slack\Surface\Component\ModalArgument;
use App\Slack\Surface\Factory\Option\QueuedUserOptionFactory;
use App\Slack\Surface\Factory\Resolver\LeaveQueue\LeaveQueueOptionsResolver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(LeaveQueueOptionsResolver::class)]
class LeaveQueueOptionsResolverTest extends KernelTestCase
{
    #[Test]
    public function itShouldSupportModalArgumentQueuedUserId(): void
    {
        $resolver = new LeaveQueueOptionsResolver($this->createStub(QueuedUserOptionFactory::class));

        $this->assertEquals(ModalArgument::QUEUED_USER_ID, $resolver->getSupportedArgument());
    }

    #[Test]
    public function itShouldReturnEmptyArrayIfQueueNotSet(): void
    {
        $resolver = new LeaveQueueOptionsResolver($this->createStub(QueuedUserOptionFactory::class));

        $this->assertEmpty($resolver->resolve());
    }

    #[Test]
    public function itShouldReturnEmptyArrayIfUserIdNotSet(): void
    {
        $resolver = new LeaveQueueOptionsResolver($this->createStub(QueuedUserOptionFactory::class))
            ->setQueue($this->createStub(Queue::class));

        $this->assertEmpty($resolver->resolve());
    }

    #[Test]
    public function itShouldCreateOptionWhereUserIdMatches(): void
    {
        $queue = $this->createMock(Queue::class);
        $queue->expects($this->once())
            ->method('getSortedUsers')
            ->willReturn([
                $skip = $this->createMock(QueuedUser::class),
                $queuedUser = $this->createMock(QueuedUser::class),
            ]);

        $skip->expects($this->once())
            ->method('getUser')
            ->willReturn(null);

        $queuedUser->expects($this->once())
            ->method('getUser')
            ->willReturn($user = $this->createMock(User::class));

        $user->expects($this->once())
            ->method('getSlackId')
            ->willReturn('userId');

        $queuedUserOptionFactory = $this->createMock(QueuedUserOptionFactory::class);
        $queuedUserOptionFactory->expects($this->once())
            ->method('create')
            ->with($queuedUser, 2)
            ->willReturn([]);

        $result = new LeaveQueueOptionsResolver($queuedUserOptionFactory)
            ->setQueue($queue)
            ->setUserId('userId')
            ->resolve();

        $this->assertCount(1, $result);
        $this->assertEquals([], $result[0]);
    }
}

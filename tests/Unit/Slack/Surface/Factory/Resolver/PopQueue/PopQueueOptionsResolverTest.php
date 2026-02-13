<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Surface\Factory\Resolver\PopQueue;

use App\Entity\Queue;
use App\Entity\QueuedUser;
use App\Slack\Surface\Component\ModalArgument;
use App\Slack\Surface\Factory\Option\QueuedUserOptionFactory;
use App\Slack\Surface\Factory\Resolver\PopQueue\PopQueueOptionsResolver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(PopQueueOptionsResolver::class)]
class PopQueueOptionsResolverTest extends KernelTestCase
{
    #[Test]
    public function itShouldSupportQueuedUserIdArgument(): void
    {
        $resolver = new PopQueueOptionsResolver($this->createStub(QueuedUserOptionFactory::class));

        $this->assertEquals(ModalArgument::QUEUED_USER_ID, $resolver->getSupportedArgument());
    }

    #[Test]
    public function itShouldReturnEmptyArrayIfQueueNotSet(): void
    {
        $resolver = new PopQueueOptionsResolver($this->createStub(QueuedUserOptionFactory::class));

        $this->assertEmpty($resolver->resolve());
    }

    #[Test]
    public function itShouldReturnOptionsForAllUsers(): void
    {
        $queue = $this->createMock(Queue::class);
        $queue->expects($this->once())
            ->method('getSortedUsers')
            ->willReturn([$queuedUser = $this->createStub(QueuedUser::class)]);

        $factory = $this->createMock(QueuedUserOptionFactory::class);
        $factory->expects($this->once())
            ->method('create')
            ->with($queuedUser, 1)
            ->willReturn([]);

        $resolver = new PopQueueOptionsResolver($factory);
        $resolver->setQueue($queue);

        $result = $resolver->resolve();

        $this->assertCount(1, $result);
        $this->assertEquals([], $result[0]);
    }
}

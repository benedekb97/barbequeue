<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Surface\Factory\Resolver\Queue;

use App\Entity\Queue;
use App\Slack\Surface\Component\ModalArgument;
use App\Slack\Surface\Factory\Resolver\Queue\MaximumEntriesDefaultValueResolver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(MaximumEntriesDefaultValueResolver::class)]
class MaximumEntriesDefaultValueResolverTest extends KernelTestCase
{
    #[Test]
    public function itShouldResolveNullArray(): void
    {
        $resolver = new MaximumEntriesDefaultValueResolver();

        $this->assertNull($resolver->resolveArray());
    }

    #[Test]
    public function itShouldSupportQueueExpiryMinutesArgument(): void
    {
        $resolver = new MaximumEntriesDefaultValueResolver();

        $this->assertEquals(ModalArgument::QUEUE_MAXIMUM_ENTRIES_PER_USER, $resolver->getSupportedArgument());
    }

    #[Test]
    public function itShouldReturnNullIfQueueNotSet(): void
    {
        $resolver = new MaximumEntriesDefaultValueResolver();

        $this->assertNull($resolver->resolveString());
    }

    #[Test]
    public function itShouldResolveNullIfQueueNotHasExpiry(): void
    {
        $queue = $this->createMock(Queue::class);
        $queue->expects($this->once())
            ->method('getMaximumEntriesPerUser')
            ->willReturn(null);

        $resolver = new MaximumEntriesDefaultValueResolver()
            ->setQueue($queue);

        $this->assertNull($resolver->resolveString());
    }

    #[Test]
    public function itShouldResolveExpiryFromQueue(): void
    {
        $queue = $this->createMock(Queue::class);
        $queue->expects($this->once())
            ->method('getMaximumEntriesPerUser')
            ->willReturn(20);

        $resolver = new MaximumEntriesDefaultValueResolver()
            ->setQueue($queue);

        $this->assertEquals('20', $resolver->resolveString());
    }
}

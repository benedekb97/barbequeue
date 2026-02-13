<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Surface\Factory\Resolver\Queue;

use App\Entity\Queue;
use App\Slack\Surface\Component\ModalArgument;
use App\Slack\Surface\Factory\Resolver\Queue\ExpiryMinutesDefaultValueResolver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(ExpiryMinutesDefaultValueResolver::class)]
class ExpiryMinutesDefaultValueResolverTest extends KernelTestCase
{
    #[Test]
    public function itShouldResolveNullArray(): void
    {
        $resolver = new ExpiryMinutesDefaultValueResolver();

        $this->assertNull($resolver->resolveArray());
    }

    #[Test]
    public function itShouldSupportQueueExpiryMinutesArgument(): void
    {
        $resolver = new ExpiryMinutesDefaultValueResolver();

        $this->assertEquals(ModalArgument::QUEUE_EXPIRY_MINUTES, $resolver->getSupportedArgument());
    }

    #[Test]
    public function itShouldReturnNullIfQueueNotSet(): void
    {
        $resolver = new ExpiryMinutesDefaultValueResolver();

        $this->assertNull($resolver->resolveString());
    }

    #[Test]
    public function itShouldResolveNullIfQueueNotHasExpiry(): void
    {
        $queue = $this->createMock(Queue::class);
        $queue->expects($this->once())
            ->method('getExpiryMinutes')
            ->willReturn(null);

        $resolver = new ExpiryMinutesDefaultValueResolver()
            ->setQueue($queue);

        $this->assertNull($resolver->resolveString());
    }

    #[Test]
    public function itShouldResolveExpiryFromQueue(): void
    {
        $queue = $this->createMock(Queue::class);
        $queue->expects($this->once())
            ->method('getExpiryMinutes')
            ->willReturn(20);

        $resolver = new ExpiryMinutesDefaultValueResolver()
            ->setQueue($queue);

        $this->assertEquals('20', $resolver->resolveString());
    }
}

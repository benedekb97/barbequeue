<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Surface\Factory\Resolver\Queue;

use App\Entity\DeploymentQueue;
use App\Entity\Queue;
use App\Enum\QueueBehaviour;
use App\Slack\Surface\Component\ModalArgument;
use App\Slack\Surface\Factory\Resolver\Queue\QueueBehaviourDefaultValueResolver;
use App\Tests\Unit\Slack\Surface\Factory\Resolver\WithOptionAssertions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(QueueBehaviourDefaultValueResolver::class)]
class QueueBehaviourDefaultValueResolverTest extends KernelTestCase
{
    use WithOptionAssertions;

    #[Test]
    public function itShouldSupportQueueBehaviourArgument(): void
    {
        $resolver = new QueueBehaviourDefaultValueResolver();

        $this->assertEquals(ModalArgument::QUEUE_BEHAVIOUR, $resolver->getSupportedArgument());
    }

    #[Test]
    public function itShouldResolveStringNull(): void
    {
        $resolver = new QueueBehaviourDefaultValueResolver();

        $this->assertNull($resolver->resolveString());
    }

    #[Test]
    public function itShouldReturnNullIfQueueNotSet(): void
    {
        $resolver = new QueueBehaviourDefaultValueResolver();

        $this->assertNull($resolver->resolveArray());
    }

    #[Test]
    public function itShouldReturnNullIfQueueNotDeploymentQueue(): void
    {
        $resolver = new QueueBehaviourDefaultValueResolver()
            ->setQueue($this->createStub(Queue::class));

        $this->assertNull($resolver->resolveArray());
    }

    #[Test]
    public function itShouldReturnOptionIfQueueSet(): void
    {
        $queue = $this->createMock(DeploymentQueue::class);
        $queue->expects($this->exactly(2))
            ->method('getBehaviour')
            ->willReturn(QueueBehaviour::ALLOW_SIMULTANEOUS);

        $resolver = new QueueBehaviourDefaultValueResolver()
            ->setQueue($queue);

        $result = $resolver->resolveArray();

        $this->assertOptionFormedCorrectly($result, 'allow-simultaneous', 'Allow simultaneous');
    }
}

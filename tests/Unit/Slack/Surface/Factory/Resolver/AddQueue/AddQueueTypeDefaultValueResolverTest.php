<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Surface\Factory\Resolver\AddQueue;

use App\Enum\Queue;
use App\Slack\Surface\Component\ModalArgument;
use App\Slack\Surface\Factory\Resolver\AddQueue\AddQueueTypeDefaultValueResolver;
use App\Tests\Unit\Slack\Surface\Factory\Resolver\WithOptionAssertions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(AddQueueTypeDefaultValueResolver::class)]
class AddQueueTypeDefaultValueResolverTest extends KernelTestCase
{
    use WithOptionAssertions;

    #[Test]
    public function itShouldSupportArgumentQueueType(): void
    {
        $resolver = new AddQueueTypeDefaultValueResolver();

        $this->assertEquals(ModalArgument::QUEUE_TYPE, $resolver->getSupportedArgument());
    }

    #[Test]
    public function itShouldResolveStringNull(): void
    {
        $resolver = new AddQueueTypeDefaultValueResolver();

        $this->assertNull($resolver->resolveString());
    }

    #[Test]
    public function itShouldResolveNullIfQueueNotSet(): void
    {
        $resolver = new AddQueueTypeDefaultValueResolver();

        $this->assertNull($resolver->resolveArray());
    }

    #[Test]
    public function itShouldResolveQueueType(): void
    {
        $resolver = new AddQueueTypeDefaultValueResolver();
        $resolver->setQueue(Queue::DEPLOYMENT);

        $result = $resolver->resolveArray();

        $this->assertOptionFormedCorrectly($result, 'deployment', 'Deployment');
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Surface\Factory\Resolver\AddQueue;

use App\Slack\Surface\Component\ModalArgument;
use App\Slack\Surface\Factory\Resolver\AddQueue\AddQueueBehaviourOptionsResolver;
use App\Tests\Unit\Slack\Surface\Factory\Resolver\WithOptionAssertions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(AddQueueBehaviourOptionsResolver::class)]
class AddQueueBehaviourOptionsResolverTest extends KernelTestCase
{
    use WithOptionAssertions;

    #[Test]
    public function itShouldSupportQueueBehaviourModalArgument(): void
    {
        $resolver = new AddQueueBehaviourOptionsResolver();

        $this->assertEquals(ModalArgument::QUEUE_BEHAVIOUR, $resolver->getSupportedArgument());
    }

    #[Test]
    public function itShouldReturnMappedBehaviours(): void
    {
        $resolver = new AddQueueBehaviourOptionsResolver();

        $result = $resolver->resolve();

        $this->assertCount(3, $result);
        $this->assertOptionFormedCorrectly($result[0], 'enforce-queue', 'Always enforce queue');
        $this->assertOptionFormedCorrectly($result[1], 'allow-jumps', 'Allow jumps');
        $this->assertOptionFormedCorrectly($result[2], 'allow-simultaneous', 'Allow simultaneous');
    }
}

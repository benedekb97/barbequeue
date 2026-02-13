<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Surface\Factory\Resolver\AddQueue;

use App\Slack\Surface\Component\ModalArgument;
use App\Slack\Surface\Factory\Resolver\AddQueue\AddQueueTypeOptionsResolver;
use App\Tests\Unit\Slack\Surface\Factory\Resolver\WithOptionAssertions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(AddQueueTypeOptionsResolver::class)]
class AddQueueTypeOptionsResolverTest extends KernelTestCase
{
    use WithOptionAssertions;

    #[Test]
    public function itShouldSupportQueueTypeArgument(): void
    {
        $resolver = new AddQueueTypeOptionsResolver();

        $this->assertEquals(ModalArgument::QUEUE_TYPE, $resolver->getSupportedArgument());
    }

    #[Test]
    public function itShouldResolveCorrectOptions(): void
    {
        $resolver = new AddQueueTypeOptionsResolver();

        $result = $resolver->resolve();

        $this->assertCount(2, $result);
        $this->assertOptionFormedCorrectly($result[0], 'simple', 'Simple');
        $this->assertOptionFormedCorrectly($result[1], 'deployment', 'Deployment');
    }
}

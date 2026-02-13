<?php

declare(strict_types=1);

namespace App\Tests\Unit\Factory;

use App\Entity\Deployment;
use App\Entity\DeploymentQueue;
use App\Factory\DeploymentFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(DeploymentFactory::class)]
class DeploymentFactoryTest extends KernelTestCase
{
    #[Test]
    public function itShouldCreateNewDeployment(): void
    {
        $factory = new DeploymentFactory();

        $queue = $this->createMock(DeploymentQueue::class);
        $queue->expects($this->once())
            ->method('addQueuedUser')
            ->willReturnCallback(function ($argument) use ($queue) {
                $this->assertInstanceOf(Deployment::class, $argument);
                $this->assertNotNull($argument->getCreatedAt());

                return $queue;
            });

        $result = $factory->createForDeploymentQueue($queue);

        $this->assertNotNull($result->getCreatedAt());
    }
}

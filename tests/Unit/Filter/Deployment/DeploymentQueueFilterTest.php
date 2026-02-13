<?php

declare(strict_types=1);

namespace App\Tests\Unit\Filter\Deployment;

use App\Entity\Deployment;
use App\Entity\DeploymentQueue;
use App\Filter\Deployment\DeploymentQueueFilter;
use App\Tests\Unit\LoggerAwareTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(DeploymentQueueFilter::class)]
class DeploymentQueueFilterTest extends LoggerAwareTestCase
{
    #[Test]
    public function itShouldFilterByWhetherQueueAllowsDeployment(): void
    {
        $callCount = 0;

        $queue = $this->createMock(DeploymentQueue::class);
        $queue->expects($this->exactly(2))
            ->method('isDeploymentAllowed')
            ->willReturnCallback(function ($argument) use (&$callCount) {
                return 2 === ++$callCount;
            });

        $deployment = $this->createMock(Deployment::class);
        $deployment->expects($this->once())
            ->method('getQueue')
            ->willReturn($queue);

        $secondDeployment = $this->createMock(Deployment::class);
        $secondDeployment->expects($this->once())
            ->method('getQueue')
            ->willReturn($queue);

        $this->expectsDebug('filtering deployments for repository');
        $this->expectsDebug('queue returned deployment allowed.');

        $filter = new DeploymentQueueFilter($this->getLogger());

        $result = $filter->filter([$deployment, $secondDeployment]);

        $this->assertCount(1, $result);
        $this->assertSame($secondDeployment, $result[0]);
    }
}

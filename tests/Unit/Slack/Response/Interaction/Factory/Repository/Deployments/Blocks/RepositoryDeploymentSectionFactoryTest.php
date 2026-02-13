<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Response\Interaction\Factory\Repository\Deployments\Blocks;

use App\Entity\Deployment;
use App\Entity\Queue;
use App\Slack\Response\Interaction\Factory\Repository\Deployments\Block\RepositoryDeploymentSectionFactory;
use App\Tests\Unit\Slack\WithBlockAssertions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(RepositoryDeploymentSectionFactory::class)]
class RepositoryDeploymentSectionFactoryTest extends KernelTestCase
{
    use WithBlockAssertions;

    #[Test]
    public function itShouldCreateSectionBlockWithBlocker(): void
    {
        $deployment = $this->createMock(Deployment::class);
        $deployment->expects($this->once())
            ->method('getUserLink')
            ->willReturn('userLink');

        $deployment->expects($this->once())
            ->method('getDescription')
            ->willReturn('description');

        $deployment->expects($this->once())
            ->method('getQueue')
            ->willReturn($queue = $this->createMock(Queue::class));

        $deployment->expects($this->once())
            ->method('getLink')
            ->willReturn('link');

        $deployment->expects($this->once())
            ->method('getExpiryMinutesLeft')
            ->willReturn(20);

        $deployment->expects($this->once())
            ->method('getBlocker')
            ->willReturn($blocker = $this->createMock(Deployment::class));

        $queue->expects($this->once())
            ->method('getName')
            ->willReturn('queueName');

        $blocker->expects($this->once())
            ->method('getUserLink')
            ->willReturn('blockerUserLink');

        $blocker->expects($this->once())
            ->method('getQueue')
            ->willReturn($blockerQueue = $this->createMock(Queue::class));

        $blockerQueue->expects($this->once())
            ->method('getName')
            ->willReturn('blockerQueueName');

        $factory = new RepositoryDeploymentSectionFactory();
        $result = $factory->create($deployment, 1)->toArray();

        $this->assertSectionBlockCorrectlyFormatted(
            '*#1* - userLink deploying `description` in the `queueName` queue. <link|See more> - _Reserved for 20 minutes._ - _Blocked by blockerUserLink in the `blockerQueueName` queue._',
            $result
        );
    }

    #[Test]
    public function itShouldNotAddExpiryTextIfExpiryMinutesLeftIsNull(): void
    {
        $deployment = $this->createMock(Deployment::class);
        $deployment->expects($this->once())
            ->method('getUserLink')
            ->willReturn('userLink');

        $deployment->expects($this->once())
            ->method('getDescription')
            ->willReturn('description');

        $deployment->expects($this->once())
            ->method('getQueue')
            ->willReturn($queue = $this->createMock(Queue::class));

        $deployment->expects($this->once())
            ->method('getLink')
            ->willReturn('link');

        $deployment->expects($this->once())
            ->method('getExpiryMinutesLeft')
            ->willReturn(null);

        $deployment->expects($this->once())
            ->method('getBlocker')
            ->willReturn($blocker = $this->createMock(Deployment::class));

        $queue->expects($this->once())
            ->method('getName')
            ->willReturn('queueName');

        $blocker->expects($this->once())
            ->method('getUserLink')
            ->willReturn('blockerUserLink');

        $blocker->expects($this->once())
            ->method('getQueue')
            ->willReturn($blockerQueue = $this->createMock(Queue::class));

        $blockerQueue->expects($this->once())
            ->method('getName')
            ->willReturn('blockerQueueName');

        $factory = new RepositoryDeploymentSectionFactory();
        $result = $factory->create($deployment, 1)->toArray();

        $this->assertSectionBlockCorrectlyFormatted(
            '*#1* - userLink deploying `description` in the `queueName` queue. <link|See more> - _Blocked by blockerUserLink in the `blockerQueueName` queue._',
            $result
        );
    }

    #[Test]
    public function itShouldNotAddBlockerTextIfNotFirstPlace(): void
    {
        $deployment = $this->createMock(Deployment::class);
        $deployment->expects($this->once())
            ->method('getUserLink')
            ->willReturn('userLink');

        $deployment->expects($this->once())
            ->method('getDescription')
            ->willReturn('description');

        $deployment->expects($this->once())
            ->method('getQueue')
            ->willReturn($queue = $this->createMock(Queue::class));

        $deployment->expects($this->once())
            ->method('getLink')
            ->willReturn('link');

        $deployment->expects($this->once())
            ->method('getExpiryMinutesLeft')
            ->willReturn(20);

        $deployment->expects($this->never())
            ->method('getBlocker')
            ->willReturn($blocker = $this->createMock(Deployment::class));

        $queue->expects($this->once())
            ->method('getName')
            ->willReturn('queueName');

        $blocker->expects($this->never())
            ->method('getUserLink')
            ->willReturn('blockerUserLink');

        $blocker->expects($this->never())
            ->method('getQueue')
            ->willReturn($blockerQueue = $this->createMock(Queue::class));

        $blockerQueue->expects($this->never())
            ->method('getName')
            ->willReturn('blockerQueueName');

        $factory = new RepositoryDeploymentSectionFactory();
        $result = $factory->create($deployment, 2)->toArray();

        $this->assertSectionBlockCorrectlyFormatted(
            '*#2* - userLink deploying `description` in the `queueName` queue. <link|See more> - _Reserved for 20 minutes._',
            $result
        );
    }
}

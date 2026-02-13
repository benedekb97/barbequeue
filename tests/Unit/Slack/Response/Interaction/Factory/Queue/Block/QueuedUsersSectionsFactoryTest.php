<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Response\Interaction\Factory\Queue\Block;

use App\Entity\Deployment;
use App\Entity\DeploymentQueue;
use App\Entity\Queue;
use App\Entity\QueuedUser;
use App\Slack\Block\Component\SectionBlock;
use App\Slack\Response\Interaction\Factory\Queue\Block\QueuedUserSection\DeploymentSectionFactory;
use App\Slack\Response\Interaction\Factory\Queue\Block\QueuedUserSection\QueuedUserSectionFactory;
use App\Slack\Response\Interaction\Factory\Queue\Block\QueuedUsersSectionsFactory;
use App\Tests\Unit\Slack\WithBlockAssertions;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(QueuedUsersSectionsFactory::class)]
class QueuedUsersSectionsFactoryTest extends KernelTestCase
{
    use WithBlockAssertions;

    #[Test]
    public function itShouldReturnASingleBlockIfQueueIsEmpty(): void
    {
        $queue = $this->createMock(Queue::class);
        $queue->expects($this->once())
            ->method('getQueuedUsers')
            ->willReturn($collection = $this->createMock(Collection::class));

        $collection->expects($this->once())
            ->method('count')
            ->willReturn(0);

        $queue->expects($this->once())
            ->method('getName')
            ->willReturn('queueName');

        $factory = new QueuedUsersSectionsFactory(
            $this->createStub(QueuedUserSectionFactory::class),
            $this->createStub(DeploymentSectionFactory::class),
        );

        $result = $factory->create($queue);

        $this->assertCount(1, $result);

        $this->assertSectionBlockCorrectlyFormatted(
            'The `queueName` queue is empty.',
            $result[0]->toArray(),
        );
    }

    #[Test]
    public function itShouldCreateASectionForEveryUserIfSimpleQueue(): void
    {
        $queue = $this->createMock(Queue::class);
        $queue->expects($this->once())
            ->method('getName')
            ->willReturn('queueName');

        $queue->expects($this->once())
            ->method('getQueuedUsers')
            ->willReturn($collection = $this->createMock(Collection::class));

        $collection->expects($this->once())
            ->method('count')
            ->willReturn(1);

        $queue->expects($this->once())
            ->method('getSortedUsers')
            ->willReturn([$queuedUser = $this->createStub(QueuedUser::class)]);

        $queuedUserSectionFactory = $this->createMock(QueuedUserSectionFactory::class);
        $queuedUserSectionFactory->expects($this->once())
            ->method('create')
            ->with($queuedUser, 1)
            ->willReturn($this->createStub(SectionBlock::class));

        $factory = new QueuedUsersSectionsFactory(
            $queuedUserSectionFactory,
            $this->createStub(DeploymentSectionFactory::class),
        );

        $result = $factory->create($queue);

        $this->assertCount(2, $result);

        $this->assertSectionBlockCorrectlyFormatted(
            'Users queued in the `queueName` queue:',
            $result[0]->toArray(),
        );
    }

    #[Test]
    public function itShouldPutPendingDeploymentsLast(): void
    {
        $queue = $this->createMock(DeploymentQueue::class);
        $queue->expects($this->once())
            ->method('getName')
            ->willReturn('queueName');

        $queue->expects($this->once())
            ->method('getQueuedUsers')
            ->willReturn($collection = $this->createMock(Collection::class));

        $collection->expects($this->once())
            ->method('count')
            ->willReturn(1);

        $queue->expects($this->once())
            ->method('getActiveDeployments')
            ->willReturn([]);

        $queue->expects($this->once())
            ->method('getPendingDeployments')
            ->willReturn([$pendingDeployment = $this->createStub(Deployment::class)]);

        $deploymentSectionFactory = $this->createMock(DeploymentSectionFactory::class);
        $deploymentSectionFactory->expects($this->once())
            ->method('create')
            ->with($pendingDeployment, 1)
            ->willReturn($this->createStub(SectionBlock::class));

        $factory = new QueuedUsersSectionsFactory(
            $this->createStub(QueuedUserSectionFactory::class),
            $deploymentSectionFactory,
        );

        $result = $factory->create($queue);

        $this->assertCount(3, $result);

        $this->assertSectionBlockCorrectlyFormatted(
            '_Nobody in the `queueName` queue is deploying at the moment._',
            $result[0]->toArray(),
        );

        $this->assertSectionBlockCorrectlyFormatted(
            'Users waiting in the `queueName` queue: ',
            $result[1]->toArray(),
        );
    }

    #[Test]
    public function itShouldPutActiveDeploymentsFirst(): void
    {
        $queue = $this->createMock(DeploymentQueue::class);
        $queue->expects($this->once())
            ->method('getName')
            ->willReturn('queueName');

        $queue->expects($this->once())
            ->method('getQueuedUsers')
            ->willReturn($collection = $this->createMock(Collection::class));

        $collection->expects($this->once())
            ->method('count')
            ->willReturn(1);

        $queue->expects($this->once())
            ->method('getActiveDeployments')
            ->willReturn([$activeDeployment = $this->createStub(Deployment::class)]);

        $queue->expects($this->once())
            ->method('getPendingDeployments')
            ->willReturn([]);

        $deploymentSectionFactory = $this->createMock(DeploymentSectionFactory::class);
        $deploymentSectionFactory->expects($this->once())
            ->method('create')
            ->with($activeDeployment)
            ->willReturn($this->createStub(SectionBlock::class));

        $factory = new QueuedUsersSectionsFactory(
            $this->createStub(QueuedUserSectionFactory::class),
            $deploymentSectionFactory,
        );

        $result = $factory->create($queue);

        $this->assertCount(3, $result);

        $this->assertSectionBlockCorrectlyFormatted(
            'Users currently deploying in the `queueName` queue: ',
            $result[0]->toArray(),
        );

        $this->assertSectionBlockCorrectlyFormatted(
            '_No deployments waiting._',
            $result[2]->toArray(),
        );
    }
}

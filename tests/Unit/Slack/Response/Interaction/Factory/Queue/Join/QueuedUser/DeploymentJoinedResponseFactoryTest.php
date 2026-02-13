<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Response\Interaction\Factory\Queue\Join\QueuedUser;

use App\Entity\Deployment;
use App\Entity\DeploymentQueue;
use App\Entity\Repository;
use App\Entity\User;
use App\Slack\Response\Interaction\Factory\Queue\Block\QueuedUsersSectionsFactory;
use App\Slack\Response\Interaction\Factory\Queue\Join\QueuedUser\DeploymentJoinedResponseFactory;
use App\Slack\Response\Interaction\Factory\Repository\Deployments\RepositoryDeploymentsSectionsFactory;
use App\Tests\Unit\Slack\WithBlockAssertions;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(DeploymentJoinedResponseFactory::class)]
class DeploymentJoinedResponseFactoryTest extends KernelTestCase
{
    use WithBlockAssertions;

    #[Test]
    public function itShouldCreateResponseWithSingleBlockIfDeploymentIsActiveWithExpiry(): void
    {
        $deployment = $this->createMock(Deployment::class);
        $deployment->expects($this->once())->method('isActive')->willReturn(true);

        $deployment->expects($this->once())
            ->method('getRepository')
            ->willReturn($repository = $this->createMock(Repository::class));

        $repository->expects($this->once())
            ->method('getName')
            ->willReturn('repositoryName');

        $deployment->expects($this->exactly(2))
            ->method('getExpiresAt')
            ->willReturn($expiresAt = $this->createMock(CarbonImmutable::class));

        $expiresAt->expects($this->once())
            ->method('diffInMinutes')
            ->with(null, true)
            ->willReturn(10.0);

        $factory = new DeploymentJoinedResponseFactory(
            $this->createStub(QueuedUsersSectionsFactory::class),
            $this->createStub(RepositoryDeploymentsSectionsFactory::class),
        );

        $result = $factory->create($deployment)->toArray();

        $this->assertArrayHasKey('blocks', $result);
        $this->assertisArray($blocks = $result['blocks']);
        $this->assertCount(1, $blocks);
        $this->assertSectionBlockCorrectlyFormatted(
            'You can start your deployment on `repositoryName` now! You have `10 minutes` before you are removed from the front of the queue.',
            $blocks[0],
        );
    }

    #[Test]
    public function itShouldCreateResponseWithSingleBlockIfDeploymentIsActive(): void
    {
        $deployment = $this->createMock(Deployment::class);
        $deployment->expects($this->once())->method('isActive')->willReturn(true);

        $deployment->expects($this->once())
            ->method('getRepository')
            ->willReturn($repository = $this->createMock(Repository::class));

        $repository->expects($this->once())
            ->method('getName')
            ->willReturn('repositoryName');

        $deployment->expects($this->once())
            ->method('getExpiresAt')
            ->willReturn(null);

        $factory = new DeploymentJoinedResponseFactory(
            $this->createStub(QueuedUsersSectionsFactory::class),
            $this->createStub(RepositoryDeploymentsSectionsFactory::class),
        );

        $result = $factory->create($deployment)->toArray();

        $this->assertArrayHasKey('blocks', $result);
        $this->assertisArray($blocks = $result['blocks']);
        $this->assertCount(1, $blocks);
        $this->assertSectionBlockCorrectlyFormatted(
            'You can start your deployment on `repositoryName` now!',
            $blocks[0],
        );
    }

    #[Test]
    public function itShouldCallRepositoryDeploymentsSectionsFactoryIfBlockerOnDifferentQueue(): void
    {
        $deployment = $this->createMock(Deployment::class);
        $deployment->expects($this->once())->method('isActive')->willReturn(false);

        $deployment->expects($this->once())
            ->method('getQueue')
            ->willReturn($queue = $this->createMock(DeploymentQueue::class));

        $deployment->expects($this->once())
            ->method('getRepository')
            ->willReturn($repository = $this->createStub(Repository::class));

        $deployment->expects($this->once())
            ->method('getBlocker')
            ->willReturn($blocker = $this->createMock(Deployment::class));

        $deployment->expects($this->once())
            ->method('getUser')
            ->willReturn($user = $this->createMock(User::class));

        $user->expects($this->once())
            ->method('getSlackId')
            ->willReturn($userId = 'userId');

        $queue->expects($this->once())
            ->method('getPlacementString')
            ->with($userId)
            ->willReturn('1st');

        $queue->expects($this->once())
            ->method('getName')
            ->willReturn('queueName');

        $blocker->expects($this->exactly(2))
            ->method('getQueue')
            ->willReturn($blockerQueue = $this->createMock(DeploymentQueue::class));

        $blocker->expects($this->once())
            ->method('getRepository')
            ->willReturn($blockerRepository = $this->createMock(Repository::class));

        $blocker->expects($this->once())
            ->method('getUserLink')
            ->willReturn('blockerUserLink');

        $blocker->expects($this->once())
            ->method('getDescription')
            ->willReturn('blockerDescription');

        $blockerQueue->expects($this->once())
            ->method('getName')
            ->willReturn('blockerQueueName');

        $blockerRepository->expects($this->once())
            ->method('getName')
            ->willReturn('blockerRepositoryName');

        $repositoryDeploymentsSectionsFactory = $this->createMock(RepositoryDeploymentsSectionsFactory::class);
        $repositoryDeploymentsSectionsFactory->expects($this->once())
            ->method('create')
            ->with($repository)
            ->willReturn([]);

        $factory = new DeploymentJoinedResponseFactory(
            $this->createStub(QueuedUsersSectionsFactory::class),
            $repositoryDeploymentsSectionsFactory,
        );

        $result = $factory->create($deployment)->toArray();

        $this->assertArrayHasKey('blocks', $result);
        $this->assertisArray($blocks = $result['blocks']);
        $this->assertCount(2, $blocks);

        $this->assertSectionBlockCorrectlyFormatted(
            'You are now 1st in the `queueName` queue. You will have to wait for blockerUserLink in the `blockerQueueName` queue to finish deploying `blockerDescription` to `blockerRepositoryName` before you can begin.',
            $blocks[0],
        );
        $this->assertDividerBlockCorrectlyFormatted($blocks[1]);
    }

    #[Test]
    public function itShouldCreateQueuedUsersSectionsIfBlockerInSameQueue(): void
    {
        $deployment = $this->createMock(Deployment::class);
        $deployment->expects($this->once())->method('isActive')->willReturn(false);

        $deployment->expects($this->once())
            ->method('getQueue')
            ->willReturn($queue = $this->createMock(DeploymentQueue::class));

        $deployment->expects($this->once())
            ->method('getRepository')
            ->willReturn($repository = $this->createStub(Repository::class));

        $deployment->expects($this->once())
            ->method('getBlocker')
            ->willReturn($blocker = $this->createMock(Deployment::class));

        $deployment->expects($this->once())
            ->method('getUser')
            ->willReturn($user = $this->createMock(User::class));

        $user->expects($this->once())
            ->method('getSlackId')
            ->willReturn($userId = 'userId');

        $queue->expects($this->once())
            ->method('getPlacementString')
            ->with($userId)
            ->willReturn('1st');

        $queue->expects($this->exactly(2))
            ->method('getName')
            ->willReturn('queueName');

        $blocker->expects($this->exactly(2))
            ->method('getQueue')
            ->willReturn($queue);

        $blocker->expects($this->once())
            ->method('getRepository')
            ->willReturn($blockerRepository = $this->createMock(Repository::class));

        $blocker->expects($this->once())
            ->method('getUserLink')
            ->willReturn('blockerUserLink');

        $blocker->expects($this->once())
            ->method('getDescription')
            ->willReturn('blockerDescription');

        $blockerRepository->expects($this->once())
            ->method('getName')
            ->willReturn('blockerRepositoryName');

        $queuedUsersSectionsFactory = $this->createMock(QueuedUsersSectionsFactory::class);
        $queuedUsersSectionsFactory->expects($this->once())
            ->method('create')
            ->with($queue)
            ->willReturn([]);

        $factory = new DeploymentJoinedResponseFactory(
            $queuedUsersSectionsFactory,
            $this->createStub(RepositoryDeploymentsSectionsFactory::class),
        );

        $result = $factory->create($deployment)->toArray();

        $this->assertArrayHasKey('blocks', $result);
        $this->assertisArray($blocks = $result['blocks']);
        $this->assertCount(2, $blocks);

        $this->assertSectionBlockCorrectlyFormatted(
            'You are now 1st in the `queueName` queue. You will have to wait for blockerUserLink in the `queueName` queue to finish deploying `blockerDescription` to `blockerRepositoryName` before you can begin.',
            $blocks[0],
        );
        $this->assertDividerBlockCorrectlyFormatted($blocks[1]);
    }
}

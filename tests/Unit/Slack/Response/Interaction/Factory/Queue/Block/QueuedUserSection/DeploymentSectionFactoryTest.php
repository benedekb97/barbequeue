<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Response\Interaction\Factory\Queue\Block\QueuedUserSection;

use App\Entity\Deployment;
use App\Entity\Queue;
use App\Entity\Repository;
use App\Slack\Response\Interaction\Factory\Queue\Block\QueuedUserSection\DeploymentSectionFactory;
use App\Tests\Unit\Slack\WithBlockAssertions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(DeploymentSectionFactory::class)]
class DeploymentSectionFactoryTest extends KernelTestCase
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
            ->method('getRepository')
            ->willReturn($repository = $this->createMock(Repository::class));

        $deployment->expects($this->once())
            ->method('getLink')
            ->willReturn('link');

        $deployment->expects($this->once())
            ->method('getExpiryMinutesLeft')
            ->willReturn(20);

        $deployment->expects($this->once())
            ->method('getBlocker')
            ->willReturn($blocker = $this->createMock(Deployment::class));

        $repository->expects($this->once())
            ->method('getName')
            ->willReturn('repositoryName');

        $blocker->expects($this->once())
            ->method('getUserLink')
            ->willReturn('blockerUserLink');

        $blocker->expects($this->once())
            ->method('getQueue')
            ->willReturn($blockerQueue = $this->createMock(Queue::class));

        $blockerQueue->expects($this->once())
            ->method('getName')
            ->willReturn('blockerQueueName');

        $factory = new DeploymentSectionFactory();
        $result = $factory->create($deployment, 1)->toArray();

        $this->assertSectionBlockCorrectlyFormatted(
            '*#1* - userLink deploying `description` to `repositoryName`. <link|See more> - _Reserved for 20 minutes._ - _Blocked by blockerUserLink in the `blockerQueueName` queue._',
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
            ->method('getRepository')
            ->willReturn($repository = $this->createMock(Repository::class));

        $deployment->expects($this->once())
            ->method('getLink')
            ->willReturn('link');

        $deployment->expects($this->once())
            ->method('getExpiryMinutesLeft')
            ->willReturn(null);

        $deployment->expects($this->once())
            ->method('getBlocker')
            ->willReturn($blocker = $this->createMock(Deployment::class));

        $repository->expects($this->once())
            ->method('getName')
            ->willReturn('repositoryName');

        $blocker->expects($this->once())
            ->method('getUserLink')
            ->willReturn('blockerUserLink');

        $blocker->expects($this->once())
            ->method('getQueue')
            ->willReturn($blockerQueue = $this->createMock(Queue::class));

        $blockerQueue->expects($this->once())
            ->method('getName')
            ->willReturn('blockerQueueName');

        $factory = new DeploymentSectionFactory();
        $result = $factory->create($deployment, 1)->toArray();

        $this->assertSectionBlockCorrectlyFormatted(
            '*#1* - userLink deploying `description` to `repositoryName`. <link|See more> - _Blocked by blockerUserLink in the `blockerQueueName` queue._',
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
            ->method('getRepository')
            ->willReturn($repository = $this->createMock(Repository::class));

        $deployment->expects($this->once())
            ->method('getLink')
            ->willReturn('link');

        $deployment->expects($this->once())
            ->method('getExpiryMinutesLeft')
            ->willReturn(20);

        $deployment->expects($this->never())
            ->method('getBlocker')
            ->willReturn($blocker = $this->createMock(Deployment::class));

        $repository->expects($this->once())
            ->method('getName')
            ->willReturn('repositoryName');

        $blocker->expects($this->never())
            ->method('getUserLink')
            ->willReturn('blockerUserLink');

        $blocker->expects($this->never())
            ->method('getQueue')
            ->willReturn($blockerQueue = $this->createMock(Queue::class));

        $blockerQueue->expects($this->never())
            ->method('getName')
            ->willReturn('blockerQueueName');

        $factory = new DeploymentSectionFactory();
        $result = $factory->create($deployment, 2)->toArray();

        $this->assertSectionBlockCorrectlyFormatted(
            '*#2* - userLink deploying `description` to `repositoryName`. <link|See more> - _Reserved for 20 minutes._',
            $result
        );
    }

    #[Test]
    public function itShouldNotAddPlaceIfNotProvided(): void
    {
        $deployment = $this->createMock(Deployment::class);
        $deployment->expects($this->once())
            ->method('getUserLink')
            ->willReturn('userLink');

        $deployment->expects($this->once())
            ->method('getDescription')
            ->willReturn('description');

        $deployment->expects($this->once())
            ->method('getRepository')
            ->willReturn($repository = $this->createMock(Repository::class));

        $deployment->expects($this->once())
            ->method('getLink')
            ->willReturn('link');

        $deployment->expects($this->once())
            ->method('getExpiryMinutesLeft')
            ->willReturn(20);

        $deployment->expects($this->never())
            ->method('getBlocker')
            ->willReturn($blocker = $this->createMock(Deployment::class));

        $repository->expects($this->once())
            ->method('getName')
            ->willReturn('repositoryName');

        $blocker->expects($this->never())
            ->method('getUserLink')
            ->willReturn('blockerUserLink');

        $blocker->expects($this->never())
            ->method('getQueue')
            ->willReturn($blockerQueue = $this->createMock(Queue::class));

        $blockerQueue->expects($this->never())
            ->method('getName')
            ->willReturn('blockerQueueName');

        $factory = new DeploymentSectionFactory();
        $result = $factory->create($deployment)->toArray();

        $this->assertSectionBlockCorrectlyFormatted(
            'userLink deploying `description` to `repositoryName`. <link|See more> - _Reserved for 20 minutes._',
            $result
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Response\Interaction\Factory\Queue\Block\QueuedUserSection;

use App\Entity\Deployment;
use App\Entity\QueuedUser;
use App\Slack\Block\Component\SectionBlock;
use App\Slack\Response\Interaction\Factory\Queue\Block\QueuedUserSection\DeploymentSectionFactory;
use App\Slack\Response\Interaction\Factory\Queue\Block\QueuedUserSection\QueuedUserSectionFactory;
use App\Slack\Response\Interaction\Factory\Queue\Block\QueuedUserSection\SimpleQueuedUserSectionFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(QueuedUserSectionFactory::class)]
class QueuedUserSectionFactoryTest extends KernelTestCase
{
    #[Test]
    public function itShouldCreateDeploymentSectionIfQueuedUserIsDeployment(): void
    {
        $queuedUser = $this->createStub(Deployment::class);

        $deploymentSectionFactory = $this->createMock(DeploymentSectionFactory::class);
        $deploymentSectionFactory->expects($this->once())
            ->method('create')
            ->with($queuedUser, $place = 1)
            ->willReturn($section = $this->createStub(SectionBlock::class));

        $simpleQueuedUserSectionFactory = $this->createMock(SimpleQueuedUserSectionFactory::class);
        $simpleQueuedUserSectionFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $factory = new QueuedUserSectionFactory(
            $simpleQueuedUserSectionFactory,
            $deploymentSectionFactory,
        );

        $result = $factory->create($queuedUser, $place);

        $this->assertSame($section, $result);
    }

    #[Test]
    public function itShouldCreateQueuedUserSectionIfQueuedUserIsNotDeployment(): void
    {
        $queuedUser = $this->createStub(QueuedUser::class);

        $deploymentSectionFactory = $this->createMock(DeploymentSectionFactory::class);
        $deploymentSectionFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $simpleQueuedUserSectionFactory = $this->createMock(SimpleQueuedUserSectionFactory::class);
        $simpleQueuedUserSectionFactory->expects($this->once())
            ->method('create')
            ->with($queuedUser, $place = 1)
            ->willReturn($section = $this->createStub(SectionBlock::class));

        $factory = new QueuedUserSectionFactory(
            $simpleQueuedUserSectionFactory,
            $deploymentSectionFactory,
        );

        $result = $factory->create($queuedUser, $place);

        $this->assertSame($section, $result);
    }
}

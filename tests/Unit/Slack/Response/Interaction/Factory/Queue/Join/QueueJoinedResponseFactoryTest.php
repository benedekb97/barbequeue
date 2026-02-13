<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Response\Interaction\Factory\Queue\Join;

use App\Entity\Deployment;
use App\Entity\QueuedUser;
use App\Slack\Response\Interaction\Factory\Queue\Join\QueuedUser\DeploymentJoinedResponseFactory;
use App\Slack\Response\Interaction\Factory\Queue\Join\QueuedUser\QueuedUserJoinedResponseFactory;
use App\Slack\Response\Interaction\Factory\Queue\Join\QueueJoinedResponseFactory;
use App\Slack\Response\Interaction\SlackInteractionResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(QueueJoinedResponseFactory::class)]
class QueueJoinedResponseFactoryTest extends KernelTestCase
{
    #[Test]
    public function itShouldCallDeploymentResponseFactoryIfQueuedUserIsDeployment(): void
    {
        $deployment = $this->createStub(Deployment::class);

        $deploymentFactory = $this->createMock(DeploymentJoinedResponseFactory::class);
        $deploymentFactory->expects($this->once())
            ->method('create')
            ->willReturn($response = $this->createStub(SlackInteractionResponse::class));

        $userFactory = $this->createMock(QueuedUserJoinedResponseFactory::class);
        $userFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $factory = new QueueJoinedResponseFactory($deploymentFactory, $userFactory);

        $result = $factory->create($deployment);

        $this->assertSame($response, $result);
    }

    #[Test]
    public function itShouldCallQueuedUserResponseFactoryIfQueuedUserIsNotDeployment(): void
    {
        $queuedUser = $this->createStub(QueuedUser::class);

        $deploymentFactory = $this->createMock(DeploymentJoinedResponseFactory::class);
        $deploymentFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $userFactory = $this->createMock(QueuedUserJoinedResponseFactory::class);
        $userFactory->expects($this->once())
            ->method('create')
            ->willReturn($response = $this->createStub(SlackInteractionResponse::class));

        $factory = new QueueJoinedResponseFactory($deploymentFactory, $userFactory);

        $result = $factory->create($queuedUser);

        $this->assertSame($response, $result);
    }
}

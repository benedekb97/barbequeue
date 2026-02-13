<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Queue\Join\Handler;

use App\Entity\Deployment;
use App\Entity\DeploymentQueue;
use App\Entity\Queue;
use App\Entity\User;
use App\Factory\DeploymentFactory;
use App\Service\Queue\Context\ContextType;
use App\Service\Queue\Context\QueueContextInterface;
use App\Service\Queue\Join\Handler\CreateDeploymentHandler;
use App\Service\Queue\Join\JoinQueueContext;
use App\Tests\Unit\LoggerAwareTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(CreateDeploymentHandler::class)]
class CreateDeploymentHandlerTest extends LoggerAwareTestCase
{
    #[Test]
    public function itShouldSupportContextWithDeploymentQueue(): void
    {
        $context = $this->createMock(QueueContextInterface::class);
        $context->expects($this->once())
            ->method('getQueue')
            ->willReturn($this->createStub(DeploymentQueue::class));

        $handler = new CreateDeploymentHandler(
            $this->createStub(DeploymentFactory::class),
            $this->getLogger(),
        );

        $this->assertTrue($handler->supports($context));
    }

    #[Test]
    public function itShouldNotSupportContextWithGenericQueue(): void
    {
        $context = $this->createMock(QueueContextInterface::class);
        $context->expects($this->once())
            ->method('getQueue')
            ->willReturn($this->createStub(Queue::class));

        $handler = new CreateDeploymentHandler(
            $this->createStub(DeploymentFactory::class),
            $this->getLogger(),
        );

        $this->assertFalse($handler->supports($context));
    }

    #[Test]
    public function itShouldReturnEarlyIfNotJoinQueueContext(): void
    {
        $context = $this->createStub(QueueContextInterface::class);

        $this->expectNotToPerformAssertions();

        $handler = new CreateDeploymentHandler(
            $this->createStub(DeploymentFactory::class),
            $this->getLogger(),
        );

        $handler->handle($context);
    }

    #[Test]
    public function itShouldReturnEarlyIfQueueNotDeploymentQueue(): void
    {
        $context = $this->createMock(JoinQueueContext::class);
        $context->expects($this->once())
            ->method('getQueue')
            ->willReturn($this->createStub(Queue::class));

        $handler = new CreateDeploymentHandler(
            $this->createStub(DeploymentFactory::class),
            $this->getLogger(),
        );

        $handler->handle($context);
    }

    #[Test]
    public function itShouldCreateDeployment(): void
    {
        $queue = $this->createMock(DeploymentQueue::class);
        $queue->expects($this->once())
            ->method('getId')
            ->willReturn($queueId = 1);

        $context = $this->createMock(JoinQueueContext::class);
        $context->expects($this->once())
            ->method('getQueue')
            ->willReturn($queue);

        $context->expects($this->once())
            ->method('getId')
            ->willReturn($contextId = 'contextId');

        $context->expects($this->once())
            ->method('getType')
            ->willReturn($type = ContextType::JOIN);

        $deployment = $this->createMock(Deployment::class);
        $deployment->expects($this->once())
            ->method('setUser')
            ->with($user = $this->createStub(User::class))
            ->willReturnSelf();

        $context->expects($this->once())
            ->method('getUser')
            ->willReturn($user);

        $context->expects($this->once())
            ->method('setQueuedUser')
            ->with($deployment);

        $deploymentFactory = $this->createMock(DeploymentFactory::class);
        $deploymentFactory->expects($this->once())
            ->method('createForDeploymentQueue')
            ->with($queue)
            ->willReturn($deployment);

        $this->expectsDebug('Creating deployment for {queue} {contextId} {contextType}', [
            'queue' => $queueId,
            'contextId' => $contextId,
            'contextType' => $type->value,
        ]);

        $handler = new CreateDeploymentHandler($deploymentFactory, $this->getLogger());

        $handler->handle($context);
    }
}

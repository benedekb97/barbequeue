<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Queue\Join\Handler;

use App\Entity\Deployment;
use App\Entity\Queue;
use App\Entity\QueuedUser;
use App\Entity\Repository;
use App\Service\Queue\Context\ContextType;
use App\Service\Queue\Context\QueueContextInterface;
use App\Service\Queue\Join\Handler\SetDeploymentInformationHandler;
use App\Service\Queue\Join\JoinQueueContext;
use App\Tests\Unit\LoggerAwareTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(SetDeploymentInformationHandler::class)]
class SetDeploymentInformationHandlerTest extends LoggerAwareTestCase
{
    #[Test]
    public function itShouldSupportJoinQueueContextWithDeployment(): void
    {
        $context = $this->createMock(JoinQueueContext::class);
        $context->expects($this->once())
            ->method('getQueuedUser')
            ->willReturn($this->createStub(Deployment::class));

        $handler = new SetDeploymentInformationHandler($this->getLogger());

        $this->assertTrue($handler->supports($context));
    }

    #[Test]
    public function itShouldNotSupportJoinQueueContextWithQueuedUser(): void
    {
        $context = $this->createMock(JoinQueueContext::class);
        $context->expects($this->once())
            ->method('getQueuedUser')
            ->willReturn($this->createStub(QueuedUser::class));

        $handler = new SetDeploymentInformationHandler($this->getLogger());

        $this->assertFalse($handler->supports($context));
    }

    #[Test]
    public function itShouldNotSupportGenericQueueContext(): void
    {
        $handler = new SetDeploymentInformationHandler($this->getLogger());

        $this->assertFalse($handler->supports($this->createStub(QueueContextInterface::class)));
    }

    #[Test]
    public function itShouldReturnEarlyIfNotJoinQueueContext(): void
    {
        $this->expectNotToPerformAssertions();

        $handler = new SetDeploymentInformationHandler($this->getLogger());

        $handler->handle($this->createStub(QueueContextInterface::class));
    }

    #[Test]
    public function itShouldReturnEarlyIfQueuedUserNotDeployment(): void
    {
        $context = $this->createMock(JoinQueueContext::class);
        $context->expects($this->once())
            ->method('getQueuedUser')
            ->willReturn($this->createStub(QueuedUser::class));

        $handler = new SetDeploymentInformationHandler($this->getLogger());

        $handler->handle($context);
    }

    #[Test]
    public function itShouldReturnEarlyIfDeploymentDescriptionEmpty(): void
    {
        $context = $this->createMock(JoinQueueContext::class);
        $context->expects($this->once())
            ->method('getQueuedUser')
            ->willReturn($this->createStub(Deployment::class));

        $context->expects($this->once())
            ->method('getDeploymentDescription')
            ->willReturn('');

        $handler = new SetDeploymentInformationHandler($this->getLogger());

        $handler->handle($context);
    }

    #[Test]
    public function itShouldReturnEarlyIfDeploymentLinkEmpty(): void
    {
        $context = $this->createMock(JoinQueueContext::class);
        $context->expects($this->once())
            ->method('getQueuedUser')
            ->willReturn($this->createStub(Deployment::class));

        $context->expects($this->once())
            ->method('getDeploymentDescription')
            ->willReturn('deploymentDescription');

        $context->expects($this->once())
            ->method('getDeploymentLink')
            ->willReturn('');

        $handler = new SetDeploymentInformationHandler($this->getLogger());

        $handler->handle($context);
    }

    #[Test]
    public function itShouldReturnEarlyIfRepositoryIsNull(): void
    {
        $context = $this->createMock(JoinQueueContext::class);
        $context->expects($this->once())
            ->method('getQueuedUser')
            ->willReturn($this->createStub(Deployment::class));

        $context->expects($this->once())
            ->method('getDeploymentDescription')
            ->willReturn('deploymentDescription');

        $context->expects($this->once())
            ->method('getDeploymentLink')
            ->willReturn('deploymentLink');

        $context->expects($this->once())
            ->method('getRepository')
            ->willReturn(null);

        $handler = new SetDeploymentInformationHandler($this->getLogger());

        $handler->handle($context);
    }

    #[Test]
    public function itShouldSetDeploymentInformationAndAddDeploymentToRepository(): void
    {
        $context = $this->createMock(JoinQueueContext::class);
        $context->expects($this->once())
            ->method('getQueuedUser')
            ->willReturn($deployment = $this->createMock(Deployment::class));

        $context->expects($this->once())
            ->method('getDeploymentDescription')
            ->willReturn($description = 'deploymentDescription');

        $context->expects($this->once())
            ->method('getDeploymentLink')
            ->willReturn($link = 'deploymentLink');

        $context->expects($this->once())
            ->method('getRepository')
            ->willReturn($repository = $this->createMock(Repository::class));

        $deployment->expects($this->once())
            ->method('getQueue')
            ->willReturn($queue = $this->createMock(Queue::class));

        $context->expects($this->once())
            ->method('getId')
            ->willReturn($contextId = 'contextId');

        $context->expects($this->once())
            ->method('getType')
            ->willReturn($type = ContextType::JOIN);

        $queue->expects($this->once())
            ->method('getId')
            ->willReturn($queueId = 1);

        $repository->expects($this->once())
            ->method('addDeployment')
            ->with($deployment)
            ->willReturnSelf();

        $deployment->expects($this->once())
            ->method('setDescription')
            ->with($description)
            ->willReturnSelf();

        $deployment->expects($this->once())
            ->method('setLink')
            ->with($link)
            ->willReturnSelf();

        $deployment->expects($this->once())
            ->method('setRepository')
            ->with($repository)
            ->willReturnSelf();

        $this->expectsDebug('Setting deployment information for {queue} {contextId} {contextType}', [
            'queue' => $queueId,
            'contextId' => $contextId,
            'contextType' => $type->value,
        ]);

        $handler = new SetDeploymentInformationHandler($this->getLogger());

        $handler->handle($context);
    }
}

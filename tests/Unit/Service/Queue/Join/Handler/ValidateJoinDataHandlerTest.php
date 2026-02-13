<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Queue\Join\Handler;

use App\Entity\DeploymentQueue;
use App\Entity\Queue;
use App\Service\Queue\Context\ContextType;
use App\Service\Queue\Context\QueueContextInterface;
use App\Service\Queue\Exception\DeploymentInformationRequiredException;
use App\Service\Queue\Join\Handler\ValidateJoinDataHandler;
use App\Service\Queue\Join\JoinQueueContext;
use App\Tests\Unit\LoggerAwareTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(ValidateJoinDataHandler::class)]
class ValidateJoinDataHandlerTest extends LoggerAwareTestCase
{
    #[Test]
    public function itShouldSupportJoinQueueContextWithDeploymentQueue(): void
    {
        $context = $this->createMock(QueueContextInterface::class);
        $context->expects($this->once())
            ->method('getType')
            ->willReturn(ContextType::JOIN);

        $context->expects($this->once())
            ->method('getQueue')
            ->willReturn($this->createStub(DeploymentQueue::class));

        $handler = new ValidateJoinDataHandler($this->getLogger());

        $this->assertTrue($handler->supports($context));
    }

    #[Test]
    public function itShouldNotSupportJoinQueueContextWithSimpleQueue(): void
    {
        $context = $this->createMock(QueueContextInterface::class);
        $context->expects($this->once())
            ->method('getType')
            ->willReturn(ContextType::JOIN);

        $context->expects($this->once())
            ->method('getQueue')
            ->willReturn($this->createStub(Queue::class));

        $handler = new ValidateJoinDataHandler($this->getLogger());

        $this->assertFalse($handler->supports($context));
    }

    #[Test]
    public function itShouldNotSupportOtherQueueContext(): void
    {
        $context = $this->createMock(QueueContextInterface::class);
        $context->expects($this->once())
            ->method('getType')
            ->willReturn(ContextType::EDIT);

        $context->expects($this->never())
            ->method('getQueue')
            ->willReturn($this->createStub(Queue::class));

        $handler = new ValidateJoinDataHandler($this->getLogger());

        $this->assertFalse($handler->supports($context));
    }

    #[Test]
    public function itShouldReturnEarlyIfQueueNotDeploymentQueue(): void
    {
        $context = $this->createMock(QueueContextInterface::class);
        $context->expects($this->once())
            ->method('getQueue')
            ->willReturn($this->createStub(Queue::class));

        $handler = new ValidateJoinDataHandler($this->getLogger());

        $handler->handle($context);
    }

    #[Test]
    public function itShouldReturnEarlyIfNotJoinQueueContext(): void
    {
        $context = $this->createMock(QueueContextInterface::class);
        $context->expects($this->once())
            ->method('getQueue')
            ->willReturn($this->createStub(DeploymentQueue::class));

        $handler = new ValidateJoinDataHandler($this->getLogger());

        $handler->handle($context);
    }

    #[Test]
    public function itShouldThrowDeploymentInformationRequiredExceptionIfDeploymentDescriptionEmpty(): void
    {
        $context = $this->createMock(JoinQueueContext::class);
        $context->expects($this->once())
            ->method('getQueue')
            ->willReturn($queue = $this->createMock(DeploymentQueue::class));

        $context->expects($this->once())
            ->method('getId')
            ->willReturN($contextId = 'contextId');

        $context->expects($this->once())
            ->method('getType')
            ->willReturn($type = ContextType::JOIN);

        $context->expects($this->once())
            ->method('getDeploymentDescription')
            ->willReturn(null);

        $queue->expects($this->once())
            ->method('getId')
            ->willReturn($queueId = 1);

        $this->expectsDebug('Validating input data for {queue} {contextId} {contextType}', [
            'queue' => $queueId,
            'contextId' => $contextId,
            'contextType' => $type->value,
        ]);

        $handler = new ValidateJoinDataHandler($this->getLogger());

        $this->expectException(DeploymentInformationRequiredException::class);

        $handler->handle($context);
    }

    #[Test]
    public function itShouldThrowDeploymentInformationRequiredExceptionIfDeploymentLinkEmpty(): void
    {
        $context = $this->createMock(JoinQueueContext::class);
        $context->expects($this->once())
            ->method('getQueue')
            ->willReturn($queue = $this->createMock(DeploymentQueue::class));

        $context->expects($this->once())
            ->method('getId')
            ->willReturN($contextId = 'contextId');

        $context->expects($this->once())
            ->method('getType')
            ->willReturn($type = ContextType::JOIN);

        $context->expects($this->once())
            ->method('getDeploymentDescription')
            ->willReturn('deploymentDescription');

        $context->expects($this->once())
            ->method('getDeploymentLink')
            ->willReturn(null);

        $queue->expects($this->once())
            ->method('getId')
            ->willReturn($queueId = 1);

        $this->expectsDebug('Validating input data for {queue} {contextId} {contextType}', [
            'queue' => $queueId,
            'contextId' => $contextId,
            'contextType' => $type->value,
        ]);

        $handler = new ValidateJoinDataHandler($this->getLogger());

        $this->expectException(DeploymentInformationRequiredException::class);

        $handler->handle($context);
    }

    #[Test]
    public function itShouldThrowDeploymentInformationRequiredExceptionIfDeploymentRepositoryIdEmpty(): void
    {
        $context = $this->createMock(JoinQueueContext::class);
        $context->expects($this->once())
            ->method('getQueue')
            ->willReturn($queue = $this->createMock(DeploymentQueue::class));

        $context->expects($this->once())
            ->method('getId')
            ->willReturN($contextId = 'contextId');

        $context->expects($this->once())
            ->method('getType')
            ->willReturn($type = ContextType::JOIN);

        $context->expects($this->once())
            ->method('getDeploymentDescription')
            ->willReturn('deploymentDescription');

        $context->expects($this->once())
            ->method('getDeploymentLink')
            ->willReturn('deploymentLink');

        $context->expects($this->once())
            ->method('getDeploymentRepositoryId')
            ->willReturn(null);

        $queue->expects($this->once())
            ->method('getId')
            ->willReturn($queueId = 1);

        $this->expectsDebug('Validating input data for {queue} {contextId} {contextType}', [
            'queue' => $queueId,
            'contextId' => $contextId,
            'contextType' => $type->value,
        ]);

        $handler = new ValidateJoinDataHandler($this->getLogger());

        $this->expectException(DeploymentInformationRequiredException::class);

        $handler->handle($context);
    }
}

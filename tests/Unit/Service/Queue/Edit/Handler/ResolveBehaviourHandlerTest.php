<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Queue\Edit\Handler;

use App\Entity\DeploymentQueue;
use App\Entity\Queue;
use App\Enum\QueueBehaviour;
use App\Service\Queue\Context\ContextType;
use App\Service\Queue\Context\QueueContextInterface;
use App\Service\Queue\Edit\EditQueueContext;
use App\Service\Queue\Edit\Handler\ResolveBehaviourHandler;
use App\Tests\Unit\LoggerAwareTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(ResolveBehaviourHandler::class)]
class ResolveBehaviourHandlerTest extends LoggerAwareTestCase
{
    #[Test]
    public function itShouldSupportContextWhereQueueIsDeploymentQueue(): void
    {
        $context = $this->createMock(QueueContextInterface::class);
        $context->expects($this->once())
            ->method('getQueue')
            ->willReturn($this->createStub(DeploymentQueue::class));

        $handler = new ResolveBehaviourHandler(
            $this->getLogger(),
        );

        $this->assertTrue($handler->supports($context));
    }

    #[Test]
    public function itShouldNotSupportContextWhereQueueIsNotDeploymentQueue(): void
    {
        $context = $this->createMock(QueueContextInterface::class);
        $context->expects($this->once())
            ->method('getQueue')
            ->willReturn($this->createStub(Queue::class));

        $handler = new ResolveBehaviourHandler(
            $this->getLogger(),
        );

        $this->assertFalse($handler->supports($context));
    }

    #[Test]
    public function itShouldReturnEarlyIfContextNotEditQueueContext(): void
    {
        $context = $this->createStub(QueueContextInterface::class);

        $this->expectNotToPerformAssertions();

        $handler = new ResolveBehaviourHandler(
            $this->getLogger(),
        );

        $handler->handle($context);
    }

    #[Test]
    public function itShouldDefaultBehaviourToEnforceQueue(): void
    {
        $context = $this->createMock(EditQueueContext::class);
        $context->expects($this->once())
            ->method('getQueueBehaviour')
            ->willReturn(null);

        $queue = $this->createMock(Queue::class);
        $queue->expects($this->once())
            ->method('getId')
            ->willReturn($queueId = 1);

        $context->expects($this->once())
            ->method('getQueue')
            ->willReturn($queue);

        $context->expects($this->once())
            ->method('getId')
            ->willReturn($contextId = 'contextId');

        $context->expects($this->once())
            ->method('getType')
            ->willReturn(ContextType::EDIT);

        $this->expectsDebug('Resolving behaviour from {behaviour} for {queue} on {contextId} {contextType}', [
            'behaviour' => null,
            'queue' => $queueId,
            'contextId' => $contextId,
            'contextType' => ContextType::EDIT->value,
        ]);

        $context->expects($this->once())
            ->method('setBehaviour')
            ->with(QueueBehaviour::ENFORCE_QUEUE);

        $handler = new ResolveBehaviourHandler($this->getLogger());

        $handler->handle($context);
    }

    #[Test]
    public function itShouldResolveBehaviourFromString(): void
    {
        $context = $this->createMock(EditQueueContext::class);
        $context->expects($this->once())
            ->method('getQueueBehaviour')
            ->willReturn('allow-jumps');

        $queue = $this->createMock(Queue::class);
        $queue->expects($this->once())
            ->method('getId')
            ->willReturn($queueId = 1);

        $context->expects($this->once())
            ->method('getQueue')
            ->willReturn($queue);

        $context->expects($this->once())
            ->method('getId')
            ->willReturn($contextId = 'contextId');

        $context->expects($this->once())
            ->method('getType')
            ->willReturn(ContextType::EDIT);

        $this->expectsDebug('Resolving behaviour from {behaviour} for {queue} on {contextId} {contextType}', [
            'behaviour' => 'allow-jumps',
            'queue' => $queueId,
            'contextId' => $contextId,
            'contextType' => ContextType::EDIT->value,
        ]);

        $context->expects($this->once())
            ->method('setBehaviour')
            ->with(QueueBehaviour::ALLOW_JUMPS);

        $handler = new ResolveBehaviourHandler($this->getLogger());

        $handler->handle($context);
    }
}

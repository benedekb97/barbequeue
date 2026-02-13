<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Queue\Pop\Handler;

use App\Entity\Queue;
use App\Entity\QueuedUser;
use App\Service\Queue\Context\ContextType;
use App\Service\Queue\Context\QueueContextInterface;
use App\Service\Queue\Exception\PopQueueInformationRequiredException;
use App\Service\Queue\Pop\Handler\ResolveQueuedUserHandler;
use App\Service\Queue\Pop\PopQueueContext;
use App\Tests\Unit\LoggerAwareTestCase;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(ResolveQueuedUserHandler::class)]
class ResolveQueuedUserHandlerTest extends LoggerAwareTestCase
{
    #[Test]
    public function itShouldSupportContextTypeLeave(): void
    {
        $context = $this->createMock(QueueContextInterface::class);
        $context->expects($this->once())
            ->method('getType')
            ->willReturn(ContextType::POP);

        $handler = new ResolveQueuedUserHandler($this->getLogger());

        $this->assertTrue($handler->supports($context));
    }

    #[Test]
    public function itShouldNotSupportOtherContextType(): void
    {
        $context = $this->createMock(QueueContextInterface::class);
        $context->expects($this->once())
            ->method('getType')
            ->willReturn(ContextType::LEAVE);

        $handler = new ResolveQueuedUserHandler($this->getLogger());

        $this->assertFalse($handler->supports($context));
    }

    #[Test]
    public function itShouldReturnEarlyIfNotPopQueueContext(): void
    {
        $this->expectNotToPerformAssertions();

        $context = $this->createStub(QueueContextInterface::class);

        $handler = new ResolveQueuedUserHandler($this->getLogger());

        $handler->handle($context);
    }

    #[Test]
    public function itShouldSetQueuedUserIfQueueOnlyHasOneUserWithIdProvided(): void
    {
        $context = $this->createMock(PopQueueContext::class);
        $context->expects($this->once())
            ->method('getQueue')
            ->willReturn($queue = $this->createMock(Queue::class));

        $context->expects($this->once())
            ->method('setQueuedUser')
            ->with($queuedUser = $this->createStub(QueuedUser::class));

        $context->expects($this->exactly(2))
            ->method('getId')
            ->willReturn($contextId = 'contextId');

        $context->expects($this->exactly(2))
            ->method('getType')
            ->willReturn($contextType = ContextType::LEAVE);

        $queue->expects($this->once())
            ->method('getQueuedUsers')
            ->willReturn($collection = $this->createMock(Collection::class));

        $queue->expects($this->exactly(2))
            ->method('getId')
            ->willReturn($queueId = 1);

        $collection->expects($this->once())
            ->method('count')
            ->willReturn(1);

        $collection->expects($this->once())
            ->method('first')
            ->willReturn($queuedUser);

        $this->expectsDebug('Resolving queued user on {queue} for {contextId} {contextType}', [
            'queue' => $queueId,
            'contextId' => $contextId,
            'contextType' => $contextType->value,
        ]);

        $this->expectsDebug('{queue} only has one queued user for {contextId} {contextType}', [
            'queue' => $queueId,
            'contextId' => $contextId,
            'contextType' => $contextType->value,
        ]);

        $handler = new ResolveQueuedUserHandler($this->getLogger());

        $handler->handle($context);
    }

    #[Test]
    public function itShouldThrowPopQueueInformationRequiredExceptionIfQueuedUserIdNotProvided(): void
    {
        $context = $this->createMock(PopQueueContext::class);
        $context->expects($this->once())
            ->method('getQueue')
            ->willReturn($queue = $this->createMock(Queue::class));

        $context->expects($this->exactly(2))
            ->method('getId')
            ->willReturn($contextId = 'contextId');

        $context->expects($this->exactly(2))
            ->method('getType')
            ->willReturn($contextType = ContextType::LEAVE);

        $context->expects($this->once())
            ->method('getQueuedUserId')
            ->willReturn(null);

        $queue->expects($this->once())
            ->method('getQueuedUsers')
            ->willReturn($collection = $this->createMock(Collection::class));

        $queue->expects($this->once())
            ->method('getId')
            ->willReturn($queueId = 1);

        $collection->expects($this->once())
            ->method('count')
            ->willReturn(2);

        $this->expectsDebug('Resolving queued user on {queue} for {contextId} {contextType}', [
            'queue' => $queueId,
            'contextId' => $contextId,
            'contextType' => $contextType->value,
        ]);

        $this->expectsDebug('Queued user ID not provided, opening modal {contextId} {contextType}', [
            'contextId' => $contextId,
            'contextType' => $contextType->value,
        ]);

        $this->expectException(PopQueueInformationRequiredException::class);

        $handler = new ResolveQueuedUserHandler($this->getLogger());

        $handler->handle($context);
    }

    #[Test]
    public function itShouldThrowPopQueueInformationRequiredExceptionIfUserIdDoesNotExistOnQueue(): void
    {
        $context = $this->createMock(PopQueueContext::class);
        $context->expects($this->once())
            ->method('getQueue')
            ->willReturn($queue = $this->createMock(Queue::class));

        $context->expects($this->exactly(2))
            ->method('getId')
            ->willReturn($contextId = 'contextId');

        $context->expects($this->exactly(2))
            ->method('getType')
            ->willReturn($contextType = ContextType::LEAVE);

        $context->expects($this->once())
            ->method('getQueuedUserId')
            ->willReturn($queuedUserId = 1);

        $queue->expects($this->once())
            ->method('getQueuedUsers')
            ->willReturn($collection = $this->createMock(Collection::class));

        $queue->expects($this->exactly(2))
            ->method('getId')
            ->willReturn($queueId = 1);

        $collection->expects($this->once())
            ->method('count')
            ->willReturn(2);

        $collection->expects($this->once())
            ->method('filter')
            ->withAnyParameters()
            ->willReturnSelf();

        $collection->expects($this->once())
            ->method('first')
            ->willReturn(false);

        $this->expectsDebug('Resolving queued user on {queue} for {contextId} {contextType}', [
            'queue' => $queueId,
            'contextId' => $contextId,
            'contextType' => $contextType->value,
        ]);

        $this->expectsWarning('Provided {queuedUserId} does not exist on {queue} for {contextId} {contextType}', [
            'queuedUserId' => $queuedUserId,
            'queue' => $queueId,
            'contextId' => $contextId,
            'contextType' => $contextType->value,
        ]);

        $this->expectException(PopQueueInformationRequiredException::class);

        $handler = new ResolveQueuedUserHandler($this->getLogger());

        $handler->handle($context);
    }

    #[Test]
    public function itShouldSetQueuedUserOnContext(): void
    {
        $context = $this->createMock(PopQueueContext::class);
        $context->expects($this->once())
            ->method('getQueue')
            ->willReturn($queue = $this->createMock(Queue::class));

        $context->expects($this->once())
            ->method('getQueuedUserId')
            ->willReturn(1);

        $context->expects($this->once())
            ->method('setQueuedUser')
            ->with($queuedUser = $this->createStub(QueuedUser::class));

        $context->expects($this->once())
            ->method('getId')
            ->willReturn($contextId = 'contextId');

        $context->expects($this->once())
            ->method('getType')
            ->willReturn($contextType = ContextType::LEAVE);

        $queue->expects($this->once())
            ->method('getQueuedUsers')
            ->willReturn($collection = $this->createMock(Collection::class));

        $collection->expects($this->once())
            ->method('count')
            ->willReturn(2);

        $collection->expects($this->once())
            ->method('filter')
            ->withAnyParameters()
            ->willReturnSelf();

        $collection->expects($this->once())
            ->method('first')
            ->willReturn($queuedUser);

        $queue->expects($this->once())
            ->method('getId')
            ->willReturn($queueId = 1);

        $this->expectsDebug('Resolving queued user on {queue} for {contextId} {contextType}', [
            'queue' => $queueId,
            'contextId' => $contextId,
            'contextType' => $contextType->value,
        ]);

        $handler = new ResolveQueuedUserHandler($this->getLogger());

        $handler->handle($context);
    }
}

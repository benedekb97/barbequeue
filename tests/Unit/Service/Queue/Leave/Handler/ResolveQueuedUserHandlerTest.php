<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Queue\Leave\Handler;

use App\Entity\Queue;
use App\Entity\QueuedUser;
use App\Service\Queue\Context\ContextType;
use App\Service\Queue\Context\QueueContextInterface;
use App\Service\Queue\Exception\LeaveQueueInformationRequiredException;
use App\Service\Queue\Leave\Handler\ResolveQueuedUserHandler;
use App\Service\Queue\Leave\LeaveQueueContext;
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
            ->willReturn(ContextType::LEAVE);

        $handler = new ResolveQueuedUserHandler($this->getLogger());

        $this->assertTrue($handler->supports($context));
    }

    #[Test]
    public function itShouldNotSupportOtherContextType(): void
    {
        $context = $this->createMock(QueueContextInterface::class);
        $context->expects($this->once())
            ->method('getType')
            ->willReturn(ContextType::EDIT);

        $handler = new ResolveQueuedUserHandler($this->getLogger());

        $this->assertFalse($handler->supports($context));
    }

    #[Test]
    public function itShouldReturnEarlyIfNotLeaveQueueContext(): void
    {
        $this->expectNotToPerformAssertions();

        $context = $this->createStub(QueueContextInterface::class);

        $handler = new ResolveQueuedUserHandler($this->getLogger());

        $handler->handle($context);
    }

    #[Test]
    public function itShouldSetQueuedUserIfQueueOnlyHasOneUserWithIdProvided(): void
    {
        $context = $this->createMock(LeaveQueueContext::class);
        $context->expects($this->once())
            ->method('getQueue')
            ->willReturn($queue = $this->createMock(Queue::class));

        $context->expects($this->once())
            ->method('getUserId')
            ->willReturN($userId = 'userId');

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
            ->method('getQueuedUsersByUserId')
            ->with($userId)
            ->willReturn($collection = $this->createMock(Collection::class));

        $queue->expects($this->once())
            ->method('getName')
            ->willReturn($queueName = 'queueName');

        $collection->expects($this->once())
            ->method('count')
            ->willReturn(1);

        $collection->expects($this->once())
            ->method('first')
            ->willReturn($queuedUser);

        $this->expectsDebug('User only has one queued user on {queue} {contextId} {contextType}', [
            'queue' => $queueName,
            'contextId' => $contextId,
            'contextType' => $contextType->value,
        ]);

        $handler = new ResolveQueuedUserHandler($this->getLogger());

        $handler->handle($context);
    }

    #[Test]
    public function itShouldThrowLeaveQueueInformationRequiredExceptionIfQueuedUserIdNotProvided(): void
    {
        $context = $this->createMock(LeaveQueueContext::class);
        $context->expects($this->once())
            ->method('getQueue')
            ->willReturn($queue = $this->createMock(Queue::class));

        $context->expects($this->once())
            ->method('getUserId')
            ->willReturN($userId = 'userId');

        $context->expects($this->once())
            ->method('getId')
            ->willReturn($contextId = 'contextId');

        $context->expects($this->once())
            ->method('getType')
            ->willReturn($contextType = ContextType::LEAVE);

        $context->expects($this->once())
            ->method('getQueuedUserId')
            ->willReturn(null);

        $queue->expects($this->once())
            ->method('getQueuedUsersByUserId')
            ->with($userId)
            ->willReturn($collection = $this->createMock(Collection::class));

        $collection->expects($this->once())
            ->method('count')
            ->willReturn(2);

        $this->expectsDebug('Queued user ID not provided, opening modal {contextId} {contextType}', [
            'contextId' => $contextId,
            'contextType' => $contextType->value,
        ]);

        $this->expectException(LeaveQueueInformationRequiredException::class);

        $handler = new ResolveQueuedUserHandler($this->getLogger());

        $handler->handle($context);
    }

    #[Test]
    public function itShouldThrowLeaveQueueInformationRequiredExceptionIfUserIdDoesNotExistOnQueue(): void
    {
        $context = $this->createMock(LeaveQueueContext::class);
        $context->expects($this->once())
            ->method('getQueue')
            ->willReturn($queue = $this->createMock(Queue::class));

        $context->expects($this->once())
            ->method('getUserId')
            ->willReturN($userId = 'userId');

        $context->expects($this->once())
            ->method('getId')
            ->willReturn($contextId = 'contextId');

        $context->expects($this->once())
            ->method('getType')
            ->willReturn($contextType = ContextType::LEAVE);

        $context->expects($this->once())
            ->method('getQueuedUserId')
            ->willReturn($queuedUserId = 1);

        $queue->expects($this->once())
            ->method('getQueuedUsersByUserId')
            ->with($userId)
            ->willReturn($collection = $this->createMock(Collection::class));

        $queue->expects($this->once())
            ->method('getName')
            ->willReturn($queueName = 'queueName');

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

        $this->expectsWarning('Provided {queuedUserId} does not exist on {queue} {contextId} {contextType}', [
            'queuedUserId' => $queuedUserId,
            'queue' => $queueName,
            'contextId' => $contextId,
            'contextType' => $contextType->value,
        ]);

        $this->expectException(LeaveQueueInformationRequiredException::class);

        $handler = new ResolveQueuedUserHandler($this->getLogger());

        $handler->handle($context);
    }

    #[Test]
    public function itShouldSetQueuedUserOnContext(): void
    {
        $context = $this->createMock(LeaveQueueContext::class);
        $context->expects($this->once())
            ->method('getQueue')
            ->willReturn($queue = $this->createMock(Queue::class));

        $context->expects($this->once())
            ->method('getUserId')
            ->willReturN($userId = 'userId');

        $context->expects($this->once())
            ->method('getQueuedUserId')
            ->willReturn(1);

        $context->expects($this->once())
            ->method('setQueuedUser')
            ->with($queuedUser = $this->createStub(QueuedUser::class));

        $queue->expects($this->once())
            ->method('getQueuedUsersByUserId')
            ->with($userId)
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

        $handler = new ResolveQueuedUserHandler($this->getLogger());

        $handler->handle($context);
    }
}

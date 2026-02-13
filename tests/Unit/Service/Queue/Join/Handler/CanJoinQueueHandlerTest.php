<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Queue\Join\Handler;

use App\Entity\Queue;
use App\Service\Queue\Context\ContextType;
use App\Service\Queue\Context\QueueContextInterface;
use App\Service\Queue\Exception\UnableToJoinQueueException;
use App\Service\Queue\Join\Handler\CanJoinQueueHandler;
use App\Tests\Unit\LoggerAwareTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(CanJoinQueueHandler::class)]
class CanJoinQueueHandlerTest extends LoggerAwareTestCase
{
    #[Test]
    public function itShouldSupportContextTypeJoin(): void
    {
        $context = $this->createMock(QueueContextInterface::class);
        $context->expects($this->once())
            ->method('getType')
            ->willReturn(ContextType::JOIN);

        $handler = new CanJoinQueueHandler($this->getLogger());

        $this->assertTrue($handler->supports($context));
    }

    #[Test]
    public function itShouldNotSupportOtherContextType(): void
    {
        $context = $this->createMock(QueueContextInterface::class);
        $context->expects($this->once())
            ->method('getType')
            ->willReturn(ContextType::LEAVE);

        $handler = new CanJoinQueueHandler($this->getLogger());

        $this->assertFalse($handler->supports($context));
    }

    #[Test]
    public function itShouldThrowUnableToJoinQueueExceptionIfCannotJoinQueue(): void
    {
        $queue = $this->createMock(Queue::class);
        $queue->expects($this->once())
            ->method('getId')
            ->willReturn($queueId = 1);

        $context = $this->createMock(QueueContextInterface::class);
        $context->expects($this->once())
            ->method('getUserId')
            ->willReturn($userId = 'userId');

        $context->expects($this->once())
            ->method('getQueue')
            ->willReturn($queue);

        $context->expects($this->once())
            ->method('getId')
            ->willReturn($contextId = 'contextId');

        $context->expects($this->once())
            ->method('getType')
            ->willReturn($contextType = ContextType::JOIN);

        $queue->expects($this->once())
            ->method('canJoin')
            ->with($userId)
            ->willReturn(false);

        $this->expectException(UnableToJoinQueueException::class);

        $this->expectsDebug('Checking whether {user} can join {queue} for {contextId} {contextType}', [
            'user' => $userId,
            'queue' => $queueId,
            'contextId' => $contextId,
            'contextType' => $contextType->value,
        ]);

        $handler = new CanJoinQueueHandler($this->getLogger());

        try {
            $handler->handle($context);
        } catch (UnableToJoinQueueException $exception) {
            $this->assertSame($queue, $exception->getQueue());

            throw $exception;
        }
    }
}

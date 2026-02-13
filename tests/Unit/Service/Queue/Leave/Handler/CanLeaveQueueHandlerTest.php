<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Queue\Leave\Handler;

use App\Entity\Queue;
use App\Service\Queue\Context\ContextType;
use App\Service\Queue\Context\QueueContextInterface;
use App\Service\Queue\Exception\UnableToLeaveQueueException;
use App\Service\Queue\Leave\Handler\CanLeaveQueueHandler;
use App\Tests\Unit\LoggerAwareTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(CanLeaveQueueHandler::class)]
class CanLeaveQueueHandlerTest extends LoggerAwareTestCase
{
    #[Test]
    public function itShouldSupportContextTypeLeave(): void
    {
        $context = $this->createMock(QueueContextInterface::class);
        $context->expects($this->once())
            ->method('getType')
            ->willReturn(ContextType::LEAVE);

        $handler = new CanLeaveQueueHandler($this->getLogger());

        $this->assertTrue($handler->supports($context));
    }

    #[Test]
    public function itShouldNotSupportOtherContextType(): void
    {
        $context = $this->createMock(QueueContextInterface::class);
        $context->expects($this->once())
            ->method('getType')
            ->willReturn(ContextType::EDIT);

        $handler = new CanLeaveQueueHandler($this->getLogger());

        $this->assertFalse($handler->supports($context));
    }

    #[Test]
    public function itShouldThrowUnableToLeaveQueueExceptionIfUserCannotLeaveQueue(): void
    {
        $context = $this->createMock(QueueContextInterface::class);
        $context->expects($this->once())
            ->method('getType')
            ->willReturn($type = ContextType::EDIT);

        $context->expects($this->once())
            ->method('getUserId')
            ->willReturn($userId = 'userId');

        $context->expects($this->once())
            ->method('getQueue')
            ->willReturn($queue = $this->createMock(Queue::class));

        $context->expects($this->once())
            ->method('getId')
            ->willReturn($contextId = 'contextId');

        $queue->expects($this->once())
            ->method('getId')
            ->willReturn($queueId = 1);

        $queue->expects($this->once())
            ->method('canLeave')
            ->with($userId)
            ->willReturn(false);

        $this->expectException(UnableToLeaveQueueException::class);

        $this->expectsDebug('Checking whether {user} can leave {queue} for {contextId} {contextType}', [
            'user' => $userId,
            'queue' => $queueId,
            'contextId' => $contextId,
            'contextType' => $type->value,
        ]);

        $handler = new CanLeaveQueueHandler($this->getLogger());

        try {
            $handler->handle($context);
        } catch (UnableToLeaveQueueException $exception) {
            $this->assertSame($queue, $exception->getQueue());

            throw $exception;
        }
    }
}

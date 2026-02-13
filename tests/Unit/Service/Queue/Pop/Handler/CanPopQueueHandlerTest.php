<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Queue\Pop\Handler;

use App\Entity\Queue;
use App\Service\Queue\Context\ContextType;
use App\Service\Queue\Context\QueueContextInterface;
use App\Service\Queue\Exception\QueueEmptyException;
use App\Service\Queue\Pop\Handler\CanPopQueueHandler;
use App\Tests\Unit\LoggerAwareTestCase;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(CanPopQueueHandler::class)]
class CanPopQueueHandlerTest extends LoggerAwareTestCase
{
    #[Test]
    public function itShouldSupportContextTypePop(): void
    {
        $context = $this->createMock(QueueContextInterface::class);
        $context->expects($this->once())
            ->method('getType')
            ->willReturn(ContextType::POP);

        $handler = new CanPopQueueHandler($this->getLogger());

        $this->assertTrue($handler->supports($context));
    }

    #[Test]
    public function itShouldNotSupportOtherContextType(): void
    {
        $context = $this->createMock(QueueContextInterface::class);
        $context->expects($this->once())
            ->method('getType')
            ->willReturn(ContextType::LEAVE);

        $handler = new CanPopQueueHandler($this->getLogger());

        $this->assertFalse($handler->supports($context));
    }

    #[Test]
    public function itShouldThrowQueueEmptyExceptionIfQueueIsEmpty(): void
    {
        $context = $this->createMock(QueueContextInterface::class);
        $context->expects($this->once())
            ->method('getQueue')
            ->willReturn($queue = $this->createMock(Queue::class));

        $context->expects($this->once())
            ->method('getType')
            ->willReturn($contextType = ContextType::POP);

        $context->expects($this->once())
            ->method('getId')
            ->willReturn($contextId = 'contextId');

        $queue->expects($this->once())
            ->method('getQueuedUsers')
            ->willReturn($collection = $this->createMock(Collection::class));

        $queue->expects($this->once())
            ->method('getId')
            ->willReturn($queueId = 1);

        $collection->expects($this->once())
            ->method('isEmpty')
            ->willReturn(true);

        $this->expectsDebug('{queue} is empty, exiting {contextId} {contextType}', [
            'queue' => $queueId,
            'contextId' => $contextId,
            'contextType' => $contextType->value,
        ]);

        $this->expectException(QueueEmptyException::class);

        $handler = new CanPopQueueHandler($this->getLogger());

        $handler->handle($context);
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Queue\Edit\Handler;

use App\Entity\Queue;
use App\Entity\Workspace;
use App\Repository\QueueRepositoryInterface;
use App\Service\Queue\Context\ContextType;
use App\Service\Queue\Context\QueueContextInterface;
use App\Service\Queue\Edit\Handler\FindQueueHandler;
use App\Tests\Unit\LoggerAwareTestCase;
use Doctrine\ORM\EntityNotFoundException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(FindQueueHandler::class)]
class FindQueueHandlerTest extends LoggerAwareTestCase
{
    #[Test]
    public function itShouldSupportEditQueueContext(): void
    {
        $context = $this->createMock(QueueContextInterface::class);
        $context->expects($this->once())
            ->method('getType')
            ->willReturn(ContextType::EDIT);

        $handler = new FindQueueHandler(
            $this->getLogger(),
            $this->createStub(QueueRepositoryInterface::class),
        );

        $this->assertTrue($handler->supports($context));
    }

    #[Test]
    public function itShouldReturnIfQueueIdentifiedIsNotNumeric(): void
    {
        $context = $this->createMock(QueueContextInterface::class);
        $context->expects($this->once())
            ->method('getQueueIdentifier')
            ->willReturn($queueName = 'queueName');

        $context->expects($this->once())
            ->method('getId')
            ->willReturn($contextId = 'contextId');

        $context->expects($this->once())
            ->method('getType')
            ->willReturn(ContextType::EDIT);

        $this->expectsError('Expected integer queue identifier, received {queueId} on {contextId} {contextType}', [
            'queueId' => $queueName,
            'contextId' => $contextId,
            'contextType' => ContextType::EDIT->value,
        ]);

        $handler = new FindQueueHandler(
            $this->getLogger(),
            $this->createStub(QueueRepositoryInterface::class),
        );

        $handler->handle($context);
    }

    #[Test]
    public function itShouldThrowEntityNotFoundExceptionIfRepositoryReturnsNull(): void
    {
        $context = $this->createMock(QueueContextInterface::class);
        $context->expects($this->once())
            ->method('getQueueIdentifier')
            ->willReturn($queueId = 1);

        $context->expects($this->exactly(2))
            ->method('getId')
            ->willReturn($contextId = 'contextId');

        $context->expects($this->exactly(2))
            ->method('getType')
            ->willReturn(ContextType::EDIT);

        $queueRepository = $this->createMock(QueueRepositoryInterface::class);
        $queueRepository->expects($this->once())
            ->method('find')
            ->with($queueId)
            ->willReturn(null);

        $this->expectsDebug('Finding {queueId} for {contextId} {contextType}', [
            'queueId' => $queueId,
            'contextId' => $contextId,
            'contextType' => ContextType::EDIT->value,
        ]);

        $this->expectsWarning('Could not find {queueId} for {contextId} {contextType}', [
            'queueId' => $queueId,
            'contextId' => $contextId,
            'contextType' => ContextType::EDIT->value,
        ]);

        $this->expectException(EntityNotFoundException::class);

        $handler = new FindQueueHandler(
            $this->getLogger(),
            $queueRepository,
        );

        $handler->handle($context);
    }

    #[Test]
    public function itShouldSetQueueAndWorkspaceOnContext(): void
    {
        $context = $this->createMock(QueueContextInterface::class);
        $context->expects($this->once())
            ->method('getQueueIdentifier')
            ->willReturn($queueId = 1);

        $context->expects($this->once())
            ->method('getId')
            ->willReturn($contextId = 'contextId');

        $context->expects($this->once())
            ->method('getType')
            ->willReturn(ContextType::EDIT);

        $queue = $this->createMock(Queue::class);
        $queue->expects($this->once())
            ->method('getWorkspace')
            ->willReturn($workspace = $this->createStub(Workspace::class));

        $context->expects($this->once())
            ->method('setQueue')
            ->with($queue);

        $context->expects($this->once())
            ->method('setWorkspace')
            ->with($workspace);

        $queueRepository = $this->createMock(QueueRepositoryInterface::class);
        $queueRepository->expects($this->once())
            ->method('find')
            ->with($queueId)
            ->willReturn($queue);

        $this->expectsDebug('Finding {queueId} for {contextId} {contextType}', [
            'queueId' => $queueId,
            'contextId' => $contextId,
            'contextType' => ContextType::EDIT->value,
        ]);

        $handler = new FindQueueHandler(
            $this->getLogger(),
            $queueRepository,
        );

        $handler->handle($context);
    }
}

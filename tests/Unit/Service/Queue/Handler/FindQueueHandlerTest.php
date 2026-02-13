<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Queue\Handler;

use App\Entity\Queue;
use App\Entity\Workspace;
use App\Repository\QueueRepositoryInterface;
use App\Service\Queue\Context\ContextType;
use App\Service\Queue\Context\QueueContextInterface;
use App\Service\Queue\Exception\QueueNotFoundException;
use App\Service\Queue\Handler\FindQueueHandler;
use App\Tests\Unit\LoggerAwareTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(FindQueueHandler::class)]
class FindQueueHandlerTest extends LoggerAwareTestCase
{
    #[Test]
    public function itShouldSupportContextWithNoWorkspace(): void
    {
        $handler = new FindQueueHandler(
            $this->createStub(QueueRepositoryInterface::class),
            $this->getLogger(),
        );

        $context = $this->createMock(QueueContextInterface::class);
        $context->expects($this->once())
            ->method('hasWorkspace')
            ->willReturn(false);

        $context->expects($this->once())
            ->method('hasQueue')
            ->willReturn(true);

        $this->assertTrue($handler->supports($context));
    }

    #[Test]
    public function itShouldSupportContextWithNoQueue(): void
    {
        $handler = new FindQueueHandler(
            $this->createStub(QueueRepositoryInterface::class),
            $this->getLogger(),
        );

        $context = $this->createMock(QueueContextInterface::class);
        $context->expects($this->never())
            ->method('hasWorkspace');

        $context->expects($this->once())
            ->method('hasQueue')
            ->willReturn(false);

        $this->assertTrue($handler->supports($context));
    }

    #[Test]
    public function itShouldNotSupportContextWithBothQueueAndWorkspace(): void
    {
        $handler = new FindQueueHandler(
            $this->createStub(QueueRepositoryInterface::class),
            $this->getLogger(),
        );

        $context = $this->createMock(QueueContextInterface::class);
        $context->expects($this->once())
            ->method('hasWorkspace')
            ->willReturn(true);

        $context->expects($this->once())
            ->method('hasQueue')
            ->willReturn(true);

        $this->assertFalse($handler->supports($context));
    }

    #[Test]
    public function itShouldThrowQueueNotFoundExceptionIfRepositoryReturnsNull(): void
    {
        $repository = $this->createMock(QueueRepositoryInterface::class);
        $repository->expects($this->once())
            ->method('findOneByNameAndTeamId')
            ->with($queueName = 'queueName', $teamId = 'teamId')
            ->willReturn(null);

        $context = $this->createMock(QueueContextInterface::class);
        $context->expects($this->once())
            ->method('getQueueIdentifier')
            ->willReturn($queueName);

        $context->expects($this->once())
            ->method('getTeamId')
            ->willReturn($teamId);

        $context->expects($this->exactly(2))
            ->method('getId')
            ->willReturn($contextId = 'contextId');

        $context->expects($this->exactly(2))
            ->method('getType')
            ->willReturn(ContextType::JOIN);

        $context->expects($this->once())
            ->method('getUserId')
            ->willReturn($userId = 'userId');

        $this->expectsDebug('Finding queue by {queueName} and {workspace} for {contextId} {contextType}', [
            'queueName' => $queueName,
            'workspace' => $teamId,
            'contextId' => $contextId,
            'contextType' => ContextType::JOIN->value,
        ]);

        $this->expectsInfo('Queue not found by {queueName} and {workspace} for {contextId} {contextType}', [
            'queueName' => $queueName,
            'workspace' => $teamId,
            'contextId' => $contextId,
            'contextType' => ContextType::JOIN->value,
        ]);

        $this->expectException(QueueNotFoundException::class);

        $handler = new FindQueueHandler($repository, $this->getLogger());

        try {
            $handler->handle($context);
        } catch (QueueNotFoundException $exception) {
            $this->assertSame($queueName, $exception->getQueueName());
            $this->assertSame($teamId, $exception->getTeamId());
            $this->assertSame($userId, $exception->getUserId());

            throw $exception;
        }
    }

    #[Test]
    public function itShouldSetQueueAndWorkspaceOnContext(): void
    {
        $queue = $this->createMock(Queue::class);
        $queue->expects($this->once())
            ->method('getWorkspace')
            ->willReturn($workspace = $this->createStub(Workspace::class));

        $repository = $this->createMock(QueueRepositoryInterface::class);
        $repository->expects($this->once())
            ->method('findOneByNameAndTeamId')
            ->with($queueName = 'queueName', $teamId = 'teamId')
            ->willReturn($queue);

        $context = $this->createMock(QueueContextInterface::class);
        $context->expects($this->once())
            ->method('getQueueIdentifier')
            ->willReturn($queueName);

        $context->expects($this->once())
            ->method('getTeamId')
            ->willReturn($teamId);

        $context->expects($this->once())
            ->method('getId')
            ->willReturn($contextId = 'contextId');

        $context->expects($this->once())
            ->method('getType')
            ->willReturn(ContextType::JOIN);

        $context->expects($this->once())
            ->method('setQueue')
            ->with($queue);

        $context->expects($this->once())
            ->method('setWorkspace')
            ->with($workspace);

        $this->expectsDebug('Finding queue by {queueName} and {workspace} for {contextId} {contextType}', [
            'queueName' => $queueName,
            'workspace' => $teamId,
            'contextId' => $contextId,
            'contextType' => ContextType::JOIN->value,
        ]);

        $handler = new FindQueueHandler($repository, $this->getLogger());

        $handler->handle($context);
    }
}

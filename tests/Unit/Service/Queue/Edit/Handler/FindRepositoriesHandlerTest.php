<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Queue\Edit\Handler;

use App\Entity\DeploymentQueue;
use App\Entity\Queue;
use App\Entity\Repository;
use App\Entity\Workspace;
use App\Repository\RepositoryRepositoryInterface;
use App\Service\Queue\Context\ContextType;
use App\Service\Queue\Context\QueueContextInterface;
use App\Service\Queue\Edit\EditQueueContext;
use App\Service\Queue\Edit\Handler\FindRepositoriesHandler;
use App\Service\Repository\Exception\RepositoryNotFoundException;
use App\Tests\Unit\LoggerAwareTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(FindRepositoriesHandler::class)]
class FindRepositoriesHandlerTest extends LoggerAwareTestCase
{
    #[Test]
    public function itShouldSupportContextWhereQueueIsDeploymentQueue(): void
    {
        $context = $this->createMock(QueueContextInterface::class);
        $context->expects($this->once())
            ->method('getQueue')
            ->willReturn($this->createStub(DeploymentQueue::class));

        $handler = new FindRepositoriesHandler(
            $this->createStub(RepositoryRepositoryInterface::class),
            $this->getLogger(),
        );

        $this->assertTrue($handler->supports($context));
    }

    #[Test]
    public function itShouldNotSupportSimpleQueue(): void
    {
        $context = $this->createMock(QueueContextInterface::class);
        $context->expects($this->once())
            ->method('getQueue')
            ->willReturn($this->createStub(Queue::class));

        $handler = new FindRepositoriesHandler(
            $this->createStub(RepositoryRepositoryInterface::class),
            $this->getLogger(),
        );

        $this->assertFalse($handler->supports($context));
    }

    #[Test]
    public function itShouldReturnIfContextNotEditQueueContext(): void
    {
        $this->expectNotToPerformAssertions();

        $context = $this->createStub(QueueContextInterface::class);

        $handler = new FindRepositoriesHandler(
            $this->createStub(RepositoryRepositoryInterface::class),
            $this->getLogger(),
        );

        $handler->handle($context);
    }

    #[Test]
    public function itShouldThrowRepositoryNotFoundExceptionIfRepositoryIdsEmpty(): void
    {
        $queue = $this->createMock(Queue::class);
        $queue->expects($this->once())
            ->method('getId')
            ->willReturn($queueId = 1);

        $context = $this->createMock(EditQueueContext::class);
        $context->expects($this->once())
            ->method('getRepositoryIds')
            ->willReturn([]);

        $context->expects($this->once())
            ->method('getQueue')
            ->willReturn($queue);

        $context->expects($this->once())
            ->method('getId')
            ->willReturn($contextId = 'contextId');

        $context->expects($this->once())
            ->method('getType')
            ->willReturn(ContextType::EDIT);

        $this->expectsError('No repository ids provided for {queue} on {contextId} {contextType}', [
            'queue' => $queueId,
            'contextId' => $contextId,
            'contextType' => ContextType::EDIT->value,
        ]);

        $this->expectException(RepositoryNotFoundException::class);

        $handler = new FindRepositoriesHandler(
            $this->createStub(RepositoryRepositoryInterface::class),
            $this->getLogger(),
        );

        $handler->handle($context);
    }

    #[Test]
    public function itShouldThrowRepositoryNotFoundExceptionIfRepositoryReturnsNoResults(): void
    {
        $queue = $this->createMock(Queue::class);
        $queue->expects($this->once())
            ->method('getId')
            ->willReturn($queueId = 1);

        $context = $this->createMock(EditQueueContext::class);
        $context->expects($this->once())
            ->method('getRepositoryIds')
            ->willReturn($repositoryIds = [1]);

        $context->expects($this->once())
            ->method('getWorkspace')
            ->willReturn($workspace = $this->createStub(Workspace::class));

        $context->expects($this->once())
            ->method('getQueue')
            ->willReturn($queue);

        $context->expects($this->exactly(2))
            ->method('getId')
            ->willReturn($contextId = 'contextId');

        $context->expects($this->exactly(2))
            ->method('getType')
            ->willReturn(ContextType::EDIT);

        $this->expectsDebug('Resolving repositories for {queue} on {contextId} {contextType}', [
            'queue' => $queueId,
            'contextId' => $contextId,
            'contextType' => ContextType::EDIT->value,
        ]);

        $this->expectsError('Provided {repositoryIds} could not be resolved on {contextId} {contextType}', [
            'contextId' => $contextId,
            'contextType' => ContextType::EDIT->value,
            'repositoryIds' => '1',
        ]);

        $repositoryRepository = $this->createMock(RepositoryRepositoryInterface::class);
        $repositoryRepository->expects($this->once())
            ->method('findByIdsAndWorkspace')
            ->with($repositoryIds, $workspace)
            ->willReturn([]);

        $this->expectException(RepositoryNotFoundException::class);

        $handler = new FindRepositoriesHandler(
            $repositoryRepository,
            $this->getLogger(),
        );

        $handler->handle($context);
    }

    #[Test]
    public function itShouldAddRepositoriesToContext(): void
    {
        $queue = $this->createMock(Queue::class);
        $queue->expects($this->once())
            ->method('getId')
            ->willReturn($queueId = 1);

        $context = $this->createMock(EditQueueContext::class);
        $context->expects($this->once())
            ->method('getRepositoryIds')
            ->willReturn($repositoryIds = [1]);

        $context->expects($this->once())
            ->method('getWorkspace')
            ->willReturn($workspace = $this->createStub(Workspace::class));

        $context->expects($this->once())
            ->method('getQueue')
            ->willReturn($queue);

        $context->expects($this->once())
            ->method('getId')
            ->willReturn($contextId = 'contextId');

        $context->expects($this->once())
            ->method('getType')
            ->willReturn(ContextType::EDIT);

        $this->expectsDebug('Resolving repositories for {queue} on {contextId} {contextType}', [
            'queue' => $queueId,
            'contextId' => $contextId,
            'contextType' => ContextType::EDIT->value,
        ]);

        $repositoryRepository = $this->createMock(RepositoryRepositoryInterface::class);
        $repositoryRepository->expects($this->once())
            ->method('findByIdsAndWorkspace')
            ->with($repositoryIds, $workspace)
            ->willReturn([$repository = $this->createStub(Repository::class)]);

        $handler = new FindRepositoriesHandler(
            $repositoryRepository,
            $this->getLogger(),
        );

        $context->expects($this->once())
            ->method('addRepository')
            ->with($repository);

        $handler->handle($context);
    }
}

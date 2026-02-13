<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Queue\Join\Handler;

use App\Entity\DeploymentQueue;
use App\Entity\Queue;
use App\Entity\Repository;
use App\Repository\RepositoryRepositoryInterface;
use App\Service\Queue\Context\ContextType;
use App\Service\Queue\Context\QueueContextInterface;
use App\Service\Queue\Exception\DeploymentInformationRequiredException;
use App\Service\Queue\Join\Handler\FindRepositoryHandler;
use App\Service\Queue\Join\JoinQueueContext;
use App\Tests\Unit\LoggerAwareTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(FindRepositoryHandler::class)]
class FindRepositoryHandlerTest extends LoggerAwareTestCase
{
    #[Test]
    public function itShouldSupportJoinContextTypeWithDeploymentQueue(): void
    {
        $context = $this->createMock(JoinQueueContext::class);
        $context->expects($this->once())
            ->method('getQueue')
            ->willReturn($this->createStub(DeploymentQueue::class));

        $context->expects($this->once())
            ->method('getRepository')
            ->willReturn(null);

        $handler = new FindRepositoryHandler(
            $this->createStub(RepositoryRepositoryInterface::class),
            $this->getLogger(),
        );

        $this->assertTrue($handler->supports($context));
    }

    #[Test]
    public function itShouldNotSupportJoinContextTypeWithNormalQueue(): void
    {
        $context = $this->createMock(JoinQueueContext::class);
        $context->expects($this->once())
            ->method('getQueue')
            ->willReturn($this->createStub(Queue::class));

        $handler = new FindRepositoryHandler(
            $this->createStub(RepositoryRepositoryInterface::class),
            $this->getLogger(),
        );

        $this->assertFalse($handler->supports($context));
    }

    #[Test]
    public function itShouldNotSupportContextWithRepository(): void
    {
        $context = $this->createMock(JoinQueueContext::class);
        $context->expects($this->once())
            ->method('getQueue')
            ->willReturn($this->createStub(DeploymentQueue::class));

        $context->expects($this->once())
            ->method('getRepository')
            ->willReturn($this->createStub(Repository::class));

        $handler = new FindRepositoryHandler(
            $this->createStub(RepositoryRepositoryInterface::class),
            $this->getLogger(),
        );

        $this->assertFalse($handler->supports($context));
    }

    #[Test]
    public function itShouldNotSupportOtherContextType(): void
    {
        $context = $this->createStub(QueueContextInterface::class);

        $handler = new FindRepositoryHandler(
            $this->createStub(RepositoryRepositoryInterface::class),
            $this->getLogger(),
        );

        $this->assertFalse($handler->supports($context));
    }

    #[Test]
    public function itShouldReturnEarlyIfNotJoinQueueContext(): void
    {
        $this->expectNotToPerformAssertions();

        $handler = new FindRepositoryHandler(
            $this->createStub(RepositoryRepositoryInterface::class),
            $this->getLogger(),
        );

        $handler->handle($this->createStub(QueueContextInterface::class));
    }

    #[Test]
    public function itShouldReturnEarlyIfNotDeploymentQueue(): void
    {
        $context = $this->createMock(JoinQueueContext::class);
        $context->expects($this->once())
            ->method('getQueue')
            ->willReturn($this->createStub(Queue::class));

        $handler = new FindRepositoryHandler(
            $this->createStub(RepositoryRepositoryInterface::class),
            $this->getLogger(),
        );

        $handler->handle($context);
    }

    #[Test]
    public function itShouldThrowDeploymentInformationRequiredExceptionIfRepositoryReturnsNull(): void
    {
        $queue = $this->createMock(DeploymentQueue::class);
        $queue->expects($this->once())
            ->method('getId')
            ->willReturn($queueId = 1);

        $context = $this->createMock(JoinQueueContext::class);
        $context->expects($this->once())
            ->method('getQueue')
            ->willReturn($queue);

        $context->expects($this->once())
            ->method('getId')
            ->willReturn($contextId = 'contextId');

        $context->expects($this->once())
            ->method('getType')
            ->willReturn($type = ContextType::JOIN);

        $context->expects($this->once())
            ->method('getDeploymentRepositoryId')
            ->willReturn($repositoryId = 1);

        $repository = $this->createMock(RepositoryRepositoryInterface::class);
        $repository->expects($this->once())
            ->method('find')
            ->with($repositoryId)
            ->willReturn(null);

        $this->expectException(DeploymentInformationRequiredException::class);

        $this->expectsDebug('Finding repository for {queue} on {contextId} {contextType}', [
            'queue' => $queueId,
            'contextId' => $contextId,
            'contextType' => $type->value,
        ]);

        $handler = new FindRepositoryHandler(
            $repository,
            $this->getLogger(),
        );

        try {
            $handler->handle($context);
        } catch (DeploymentInformationRequiredException $exception) {
            $this->assertSame($queue, $exception->getQueue());

            throw $exception;
        }
    }

    #[Test]
    public function itShouldSetRepositoryOnContext(): void
    {
        $queue = $this->createMock(DeploymentQueue::class);
        $queue->expects($this->once())
            ->method('getId')
            ->willReturn($queueId = 1);

        $context = $this->createMock(JoinQueueContext::class);
        $context->expects($this->once())
            ->method('getQueue')
            ->willReturn($queue);

        $context->expects($this->once())
            ->method('getId')
            ->willReturn($contextId = 'contextId');

        $context->expects($this->once())
            ->method('getType')
            ->willReturn($type = ContextType::JOIN);

        $context->expects($this->once())
            ->method('getDeploymentRepositoryId')
            ->willReturn($repositoryId = 1);

        $repositoryRepository = $this->createMock(RepositoryRepositoryInterface::class);
        $repositoryRepository->expects($this->once())
            ->method('find')
            ->with($repositoryId)
            ->willReturn($repository = $this->createStub(Repository::class));

        $context->expects($this->once())
            ->method('setRepository')
            ->with($repository);

        $this->expectsDebug('Finding repository for {queue} on {contextId} {contextType}', [
            'queue' => $queueId,
            'contextId' => $contextId,
            'contextType' => $type->value,
        ]);

        $handler = new FindRepositoryHandler(
            $repositoryRepository,
            $this->getLogger(),
        );

        $handler->handle($context);
    }
}

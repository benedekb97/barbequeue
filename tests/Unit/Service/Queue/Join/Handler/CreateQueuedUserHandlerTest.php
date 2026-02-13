<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Queue\Join\Handler;

use App\Entity\DeploymentQueue;
use App\Entity\Queue;
use App\Entity\QueuedUser;
use App\Entity\User;
use App\Factory\QueuedUserFactory;
use App\Service\Queue\Context\ContextType;
use App\Service\Queue\Context\QueueContextInterface;
use App\Service\Queue\Join\Handler\CreateQueuedUserHandler;
use App\Service\Queue\Join\JoinQueueContext;
use App\Tests\Unit\LoggerAwareTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(CreateQueuedUserHandler::class)]
class CreateQueuedUserHandlerTest extends LoggerAwareTestCase
{
    #[Test]
    public function itShouldSupportContextWhereQueueNotDeploymentQueue(): void
    {
        $context = $this->createMock(QueueContextInterface::class);
        $context->expects($this->once())
            ->method('getQueue')
            ->willReturn($this->createStub(Queue::class));

        $handler = new CreateQueuedUserHandler(
            $this->createStub(QueuedUserFactory::class),
            $this->getLogger(),
        );

        $this->assertTrue($handler->supports($context));
    }

    #[Test]
    public function itShouldNotSupportContextWithDeploymentQueue(): void
    {
        $context = $this->createMock(QueueContextInterface::class);
        $context->expects($this->once())
            ->method('getQueue')
            ->willReturn($this->createStub(DeploymentQueue::class));

        $handler = new CreateQueuedUserHandler(
            $this->createStub(QueuedUserFactory::class),
            $this->getLogger(),
        );

        $this->assertFalse($handler->supports($context));
    }

    #[Test]
    public function itShouldReturnEarlyIfNotJoinQueueContext(): void
    {
        $this->expectNotToPerformAssertions();

        $context = $this->createStub(QueueContextInterface::class);

        $handler = new CreateQueuedUserHandler(
            $this->createStub(QueuedUserFactory::class),
            $this->getLogger(),
        );

        $handler->handle($context);
    }

    #[Test]
    public function itShouldCreateQueuedUser(): void
    {
        $queue = $this->createMock(Queue::class);
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

        $queuedUser = $this->createMock(QueuedUser::class);
        $queuedUser->expects($this->once())
            ->method('setUser')
            ->with($user = $this->createStub(User::class))
            ->willReturnSelf();

        $context->expects($this->once())
            ->method('getUser')
            ->willReturn($user);

        $context->expects($this->once())
            ->method('setQueuedUser')
            ->with($queuedUser);

        $queuedUserFactory = $this->createMock(QueuedUserFactory::class);
        $queuedUserFactory->expects($this->once())
            ->method('createForQueue')
            ->with($queue)
            ->willReturn($queuedUser);

        $this->expectsDebug('Creating queued user for simple {queue} {contextId} {contextType}', [
            'queue' => $queueId,
            'contextId' => $contextId,
            'contextType' => $type->value,
        ]);

        $handler = new CreateQueuedUserHandler($queuedUserFactory, $this->getLogger());

        $handler->handle($context);
    }
}

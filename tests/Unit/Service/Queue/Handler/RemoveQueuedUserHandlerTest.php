<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Queue\Handler;

use App\Entity\Queue;
use App\Entity\QueuedUser;
use App\Service\Queue\Context\ContextType;
use App\Service\Queue\Context\QueueContextInterface;
use App\Service\Queue\Handler\RemoveQueuedUserHandler;
use App\Service\Queue\Leave\LeaveQueueContext;
use App\Tests\Unit\LoggerAwareTestCase;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(RemoveQueuedUserHandler::class)]
class RemoveQueuedUserHandlerTest extends LoggerAwareTestCase
{
    #[Test]
    public function itShouldSupportContextTypeLeave(): void
    {
        $context = $this->createMock(QueueContextInterface::class);
        $context->expects($this->once())
            ->method('getType')
            ->willReturn(ContextType::LEAVE);

        $handler = new RemoveQueuedUserHandler(
            $this->createStub(EntityManagerInterface::class),
            $this->getLogger(),
        );

        $this->assertTrue($handler->supports($context));
    }

    #[Test]
    public function itShouldSupportContextTypePop(): void
    {
        $context = $this->createMock(QueueContextInterface::class);
        $context->expects($this->once())
            ->method('getType')
            ->willReturn(ContextType::POP);

        $handler = new RemoveQueuedUserHandler(
            $this->createStub(EntityManagerInterface::class),
            $this->getLogger(),
        );

        $this->assertTrue($handler->supports($context));
    }

    #[Test]
    public function itShouldNotSupportOtherContextType(): void
    {
        $context = $this->createMock(QueueContextInterface::class);
        $context->expects($this->once())
            ->method('getType')
            ->willReturn(ContextType::EDIT);

        $handler = new RemoveQueuedUserHandler(
            $this->createStub(EntityManagerInterface::class),
            $this->getLogger(),
        );

        $this->assertFalse($handler->supports($context));
    }

    #[Test]
    public function itShouldReturnEarlyIfContextGenericQueueContext(): void
    {
        $this->expectNotToPerformAssertions();

        $context = $this->createStub(QueueContextInterface::class);

        $handler = new RemoveQueuedUserHandler(
            $this->createStub(EntityManagerInterface::class),
            $this->getLogger(),
        );

        $handler->handle($context);
    }

    #[Test]
    public function itShouldRemoveQueuedUser(): void
    {
        $context = $this->createMock(LeaveQueueContext::class);
        $context->expects($this->once())
            ->method('getId')
            ->willReturn($contextId = 'contextId');

        $context->expects($this->once())
            ->method('getType')
            ->willReturn($contextType = ContextType::LEAVE);

        $queuedUser = $this->createMock(QueuedUser::class);
        $queuedUser->expects($this->once())
            ->method('getId')
            ->willReturn($queuedUserId = 1);

        $queue = $this->createMock(Queue::class);
        $queue->expects($this->once())
            ->method('removeQueuedUser')
            ->with($queuedUser)
            ->willReturnSelf();

        $queue->expects($this->once())
            ->method('getId')
            ->willReturn($queueId = 1);

        $context->expects($this->once())
            ->method('getQueue')
            ->willReturn($queue);

        $context->expects($this->once())
            ->method('getQueuedUser')
            ->willReturn($queuedUser);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->once())
            ->method('remove')
            ->with($queuedUser);

        $this->expectsDebug('Removing {queuedUser} from {queue} {contextId} {contextType}', [
            'queuedUser' => $queuedUserId,
            'queue' => $queueId,
            'contextId' => $contextId,
            'contextType' => $contextType->value,
        ]);

        $handler = new RemoveQueuedUserHandler($entityManager, $this->getLogger());

        $handler->handle($context);
    }
}

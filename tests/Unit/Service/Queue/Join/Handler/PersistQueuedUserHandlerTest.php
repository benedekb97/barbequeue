<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Queue\Join\Handler;

use App\Entity\Queue;
use App\Entity\QueuedUser;
use App\Service\Queue\Context\ContextType;
use App\Service\Queue\Context\QueueContextInterface;
use App\Service\Queue\Join\Handler\PersistQueuedUserHandler;
use App\Service\Queue\Join\JoinQueueContext;
use App\Tests\Unit\LoggerAwareTestCase;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Psr\Log\LoggerInterface;

#[CoversClass(PersistQueuedUserHandler::class)]
class PersistQueuedUserHandlerTest extends LoggerAwareTestCase
{
    #[Test]
    public function itShouldSupportJoinQueueContext(): void
    {
        $handler = new PersistQueuedUserHandler(
            $this->createStub(EntityManagerInterface::class),
            $this->createStub(LoggerInterface::class),
        );

        $this->assertTrue($handler->supports($this->createStub(JoinQueueContext::class)));
    }

    #[Test]
    public function itShouldNotSupportGenericQueueContext(): void
    {
        $handler = new PersistQueuedUserHandler(
            $this->createStub(EntityManagerInterface::class),
            $this->createStub(LoggerInterface::class),
        );

        $this->assertFalse($handler->supports($this->createStub(QueueContextInterface::class)));
    }

    #[Test]
    public function itShouldReturnEarlyIfNotJoinQueueContext(): void
    {
        $this->expectNotToPerformAssertions();

        $handler = new PersistQueuedUserHandler(
            $this->createStub(EntityManagerInterface::class),
            $this->createStub(LoggerInterface::class),
        );

        $handler->handle($this->createStub(QueueContextInterface::class));
    }

    #[Test]
    public function itShouldPersistQueuedUser(): void
    {
        $context = $this->createMock(JoinQueueContext::class);
        $context->expects($this->once())
            ->method('getQueuedUser')
            ->willReturn($queuedUser = $this->createStub(QueuedUser::class));

        $queue = $this->createMock(Queue::class);
        $queue->expects($this->once())
            ->method('getId')
            ->willReturn($queueId = 1);

        $context->expects($this->once())
            ->method('getQueue')
            ->willReturn($queue);

        $context->expects($this->once())
            ->method('getId')
            ->willReturn($contextId = 'contextId');

        $context->expects($this->once())
            ->method('getType')
            ->willReturn($type = ContextType::JOIN);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->once())
            ->method('persist')
            ->with($queuedUser);

        $this->expectsDebug('Persisting queued user for {queue} {contextId} {contextType}', [
            'queue' => $queueId,
            'contextId' => $contextId,
            'contextType' => $type->value,
        ]);

        $handler = new PersistQueuedUserHandler(
            $entityManager,
            $this->getLogger(),
        );

        $handler->handle($context);
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Queue\Edit\Handler;

use App\Entity\Queue;
use App\Service\Queue\Context\ContextType;
use App\Service\Queue\Context\QueueContextInterface;
use App\Service\Queue\Edit\Handler\PersistQueueHandler;
use App\Tests\Unit\LoggerAwareTestCase;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(PersistQueueHandler::class)]
class PersistQueueHandlerTest extends LoggerAwareTestCase
{
    #[Test]
    public function itShouldSupportEditContextType(): void
    {
        $context = $this->createMock(QueueContextInterface::class);
        $context->expects($this->once())
            ->method('getType')
            ->willReturn(ContextType::EDIT);

        $handler = new PersistQueueHandler(
            $this->createStub(EntityManagerInterface::class),
            $this->getLogger(),
        );

        $this->assertTrue($handler->supports($context));
    }

    #[Test]
    public function itShouldPersistTheQueueFromContext(): void
    {
        $queue = $this->createMock(Queue::class);
        $queue->expects($this->once())
            ->method('getId')
            ->willReturn($queueId = 1);

        $context = $this->createMock(QueueContextInterface::class);
        $context->expects($this->exactly(2))
            ->method('getQueue')
            ->willReturn($queue);

        $context->expects($this->once())
            ->method('getId')
            ->willReturn($contextId = 'contextId');

        $context->expects($this->once())
            ->method('getType')
            ->willReturn(ContextType::EDIT);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->once())
            ->method('persist')
            ->with($queue);

        $this->expectsDebug('Persisting {queue} for {contextId} {contextType}', [
            'queue' => $queueId,
            'contextId' => $contextId,
            'contextType' => ContextType::EDIT->value,
        ]);

        $handler = new PersistQueueHandler(
            $entityManager,
            $this->getLogger(),
        );

        $handler->handle($context);
    }
}

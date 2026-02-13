<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Queue\Edit\Handler;

use App\Entity\Queue;
use App\Service\Queue\Context\ContextType;
use App\Service\Queue\Context\QueueContextInterface;
use App\Service\Queue\Edit\EditQueueContext;
use App\Service\Queue\Edit\Handler\SetQueueInformationHandler;
use App\Tests\Unit\LoggerAwareTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(SetQueueInformationHandler::class)]
class SetQueueInformationHandlerTest extends LoggerAwareTestCase
{
    #[Test]
    public function itShouldSupportEditContextType(): void
    {
        $context = $this->createMock(QueueContextInterface::class);
        $context->expects($this->once())
            ->method('getType')
            ->willReturn(ContextType::EDIT);

        $handler = new SetQueueInformationHandler($this->getLogger());

        $this->assertTrue($handler->supports($context));
    }

    #[Test]
    public function itShouldNotSupportOtherContextType(): void
    {
        $context = $this->createMock(QueueContextInterface::class);
        $context->expects($this->once())
            ->method('getType')
            ->willReturn(ContextType::LEAVE);

        $handler = new SetQueueInformationHandler($this->getLogger());

        $this->assertFalse($handler->supports($context));
    }

    #[Test]
    public function itShouldReturnEarlyIfNotEditQueueContext(): void
    {
        $this->expectNotToPerformAssertions();

        $context = $this->createStub(QueueContextInterface::class);

        $handler = new SetQueueInformationHandler($this->getLogger());

        $handler->handle($context);
    }

    #[Test]
    public function itShouldSetQueueInformation(): void
    {
        $queue = $this->createMock(Queue::class);
        $queue->expects($this->once())
            ->method('getId')
            ->willReturn($queueId = 1);

        $queue->expects($this->once())
            ->method('setMaximumEntriesPerUser')
            ->with($maxEntries = null)
            ->willReturnSelf();

        $queue->expects($this->once())
            ->method('setExpiryMinutes')
            ->with($expiry = 20)
            ->willReturnSelf();

        $context = $this->createMock(EditQueueContext::class);
        $context->expects($this->once())
            ->method('getQueue')
            ->willReturn($queue);

        $context->expects($this->once())
            ->method('getId')
            ->willReturn($contextId = 'contextId');

        $context->expects($this->once())
            ->method('getType')
            ->willReturn(ContextType::EDIT);

        $context->expects($this->once())
            ->method('getMaximumEntriesPerUser')
            ->willReturn($maxEntries);

        $context->expects($this->once())
            ->method('getExpiryMinutes')
            ->willReturn($expiry);

        $this->expectsDebug(
            'Setting information on {queue} for {contextId} {contextType}',
            [
                'queue' => $queueId,
                'contextId' => $contextId,
                'contextType' => ContextType::EDIT->value,
            ]
        );

        $handler = new SetQueueInformationHandler($this->getLogger());

        $handler->handle($context);
    }
}

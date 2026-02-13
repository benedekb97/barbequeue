<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Queue\Join\Handler;

use App\Entity\Queue;
use App\Entity\QueuedUser;
use App\Service\Queue\Context\ContextType;
use App\Service\Queue\Context\QueueContextInterface;
use App\Service\Queue\Join\Handler\SetExpiryMinutesHandler;
use App\Service\Queue\Join\JoinQueueContext;
use App\Tests\Unit\LoggerAwareTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(SetExpiryMinutesHandler::class)]
class SetExpiryMinutesHandlerTest extends LoggerAwareTestCase
{
    #[Test]
    public function itShouldSupportJoinQueueContextWithRequiredMinutes(): void
    {
        $context = $this->createMock(JoinQueueContext::class);
        $context->expects($this->once())
            ->method('getRequiredMinutes')
            ->willReturn(1);

        $handler = new SetExpiryMinutesHandler($this->getLogger());

        $this->assertTrue($handler->supports($context));
    }

    #[Test]
    public function itShouldSupportJoinQueueContextWithExpiryMinutesOnQueue(): void
    {
        $context = $this->createMock(JoinQueueContext::class);
        $context->expects($this->once())
            ->method('getRequiredMinutes')
            ->willReturn(null);

        $context->expects($this->once())
            ->method('getQueue')
            ->willReturn($queue = $this->createMock(Queue::class));

        $queue->expects($this->once())
            ->method('getExpiryMinutes')
            ->willReturn(1);

        $handler = new SetExpiryMinutesHandler($this->getLogger());

        $this->assertTrue($handler->supports($context));
    }

    #[Test]
    public function itShouldNotSupportJoinQueueContextWithNoMinutesRequirements(): void
    {
        $context = $this->createMock(JoinQueueContext::class);
        $context->expects($this->once())
            ->method('getRequiredMinutes')
            ->willReturn(null);

        $context->expects($this->once())
            ->method('getQueue')
            ->willReturn($queue = $this->createMock(Queue::class));

        $queue->expects($this->once())
            ->method('getExpiryMinutes')
            ->willReturn(null);

        $handler = new SetExpiryMinutesHandler($this->getLogger());

        $this->assertFalse($handler->supports($context));
    }

    #[Test]
    public function itShouldNotSupportGenericQueueContext(): void
    {
        $handler = new SetExpiryMinutesHandler($this->getLogger());

        $this->assertFalse($handler->supports($this->createStub(QueueContextInterface::class)));
    }

    #[Test]
    public function itShouldReturnEarlyIfNotJoinQueueContext(): void
    {
        $this->expectNotToPerformAssertions();

        $handler = new SetExpiryMinutesHandler($this->getLogger());

        $handler->handle($this->createStub(QueueContextInterface::class));
    }

    #[Test, DataProvider('provideExpiryVariations')]
    public function itShouldSetExpiryMinutesOnQueuedUser(?int $requiredMinutes, ?int $queueExpiry, ?int $expected): void
    {
        $context = $this->createMock(JoinQueueContext::class);
        $context->expects($this->once())
            ->method('getQueue')
            ->willReturn($queue = $this->createMock(Queue::class));

        $context->expects($this->once())
            ->method('getRequiredMinutes')
            ->willReturn($requiredMinutes);

        $context->expects($this->once())
            ->method('getId')
            ->willReturn($contextId = 'contextId');

        $context->expects($this->once())
            ->method('getType')
            ->willReturn($type = ContextType::JOIN);

        $context->expects($this->once())
            ->method('getQueuedUser')
            ->willReturn($queuedUser = $this->createMock(QueuedUser::class));

        $queue->expects($this->once())
            ->method('getExpiryMinutes')
            ->willReturn($queueExpiry);

        $queue->expects($this->once())
            ->method('getId')
            ->willReturn($queueId = 1);

        $queuedUser->expects($this->once())
            ->method('setExpiryMinutes')
            ->with($expected)
            ->willReturnSelf();

        $this->expectsDebug(
            'Setting expiry minutes for queued user on {queue} to {expiryMinutes} {contextId} {contextType}',
            [
                'queue' => $queueId,
                'expiryMinutes' => $expected,
                'contextId' => $contextId,
                'contextType' => $type->value,
            ],
        );

        $handler = new SetExpiryMinutesHandler($this->getLogger());

        $handler->handle($context);
    }

    public static function provideExpiryVariations(): array
    {
        return [
            [null, 10, 10],
            [10, null, 10],
            [10, 20, 10],
            [20, 10, 10],
            [null, null, null],
        ];
    }
}

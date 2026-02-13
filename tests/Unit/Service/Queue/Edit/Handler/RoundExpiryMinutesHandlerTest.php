<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Queue\Edit\Handler;

use App\Service\Queue\Context\ContextType;
use App\Service\Queue\Context\QueueContextInterface;
use App\Service\Queue\Edit\EditQueueContext;
use App\Service\Queue\Edit\Handler\RoundExpiryMinutesHandler;
use App\Tests\Unit\LoggerAwareTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(RoundExpiryMinutesHandler::class)]
class RoundExpiryMinutesHandlerTest extends LoggerAwareTestCase
{
    #[Test]
    public function itShouldSupportEditQueueContextWithExpiryMinutes(): void
    {
        $context = $this->createMock(EditQueueContext::class);
        $context->expects($this->once())
            ->method('getExpiryMinutes')
            ->willReturn(1);

        $handler = new RoundExpiryMinutesHandler($this->getLogger());

        $this->assertTrue($handler->supports($context));
    }

    #[Test]
    public function itShouldNotSupportEditQueueContextWithNoExpiryMinutes(): void
    {
        $context = $this->createMock(EditQueueContext::class);
        $context->expects($this->once())
            ->method('getExpiryMinutes')
            ->willReturn(null);

        $handler = new RoundExpiryMinutesHandler($this->getLogger());

        $this->assertFalse($handler->supports($context));
    }

    #[Test]
    public function itShouldNotSupportOtherContextClass(): void
    {
        $context = $this->createStub(QueueContextInterface::class);

        $handler = new RoundExpiryMinutesHandler($this->getLogger());

        $this->assertFalse($handler->supports($context));
    }

    #[Test]
    public function itShouldReturnEarlyIfContextNotEditQueueContext(): void
    {
        $context = $this->createStub(QueueContextInterface::class);

        $handler = new RoundExpiryMinutesHandler($this->getLogger());

        $this->expectNotToPerformAssertions();

        $handler->handle($context);
    }

    #[Test]
    public function itShouldNotSetExpiryMinutesIfExpiryAlreadyDivisibleByFive(): void
    {
        $context = $this->createMock(EditQueueContext::class);
        $context->expects($this->once())
            ->method('getExpiryMinutes')
            ->willReturn(5);

        $context->expects($this->never())
            ->method('setExpiryMinutes')
            ->withAnyParameters();

        $context->expects($this->once())
            ->method('getId')
            ->willReturn($contextId = 'contextId');

        $context->expects($this->once())
            ->method('getType')
            ->willReturn(ContextType::EDIT);

        $this->expectsDebug(
            'Rounding up expiry minutes from {expiryMinutes} to closest 5 for {contextId} {contextType}',
            [
                'expiryMinutes' => 5,
                'contextId' => $contextId,
                'contextType' => ContextType::EDIT->value,
            ]
        );

        $handler = new RoundExpiryMinutesHandler($this->getLogger());

        $handler->handle($context);
    }

    #[Test]
    public function itShouldRoundExpiryMinutesIfExpiryNotDivisibleByFive(): void
    {
        $context = $this->createMock(EditQueueContext::class);
        $context->expects($this->once())
            ->method('getExpiryMinutes')
            ->willReturn(6);

        $context->expects($this->once())
            ->method('setExpiryMinutes')
            ->with(10);

        $context->expects($this->once())
            ->method('getId')
            ->willReturn($contextId = 'contextId');

        $context->expects($this->once())
            ->method('getType')
            ->willReturn(ContextType::EDIT);

        $this->expectsDebug(
            'Rounding up expiry minutes from {expiryMinutes} to closest 5 for {contextId} {contextType}',
            [
                'expiryMinutes' => 6,
                'contextId' => $contextId,
                'contextType' => ContextType::EDIT->value,
            ]
        );

        $handler = new RoundExpiryMinutesHandler($this->getLogger());

        $handler->handle($context);
    }
}

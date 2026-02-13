<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Queue\Leave;

use App\Service\Queue\Leave\Handler\LeaveQueueHandlerInterface;
use App\Service\Queue\Leave\LeaveQueueContext;
use App\Service\Queue\Leave\LeaveQueueHandler;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(LeaveQueueHandler::class)]
class LeaveQueueHandlerTest extends KernelTestCase
{
    #[Test]
    public function itShouldCallSupportedHandlers(): void
    {
        $supportedHandler = $this->createMock(LeaveQueueHandlerInterface::class);
        $supportedHandler->expects($this->once())
            ->method('supports')
            ->with($context = $this->createStub(LeaveQueueContext::class))
            ->willReturn(true);

        $supportedHandler->expects($this->once())
            ->method('handle')
            ->with($context);

        $unsupportedHandler = $this->createMock(LeaveQueueHandlerInterface::class);
        $unsupportedHandler->expects($this->once())
            ->method('supports')
            ->with($context)
            ->willReturn(false);

        $unsupportedHandler->expects($this->never())
            ->method('handle')
            ->with($context);

        $handler = new LeaveQueueHandler([$supportedHandler, $unsupportedHandler]);

        $handler->handle($context);
    }
}

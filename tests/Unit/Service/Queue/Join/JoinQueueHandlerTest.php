<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Queue\Join;

use App\Service\Queue\Join\Handler\JoinQueueHandlerInterface;
use App\Service\Queue\Join\JoinQueueContext;
use App\Service\Queue\Join\JoinQueueHandler;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(JoinQueueHandler::class)]
class JoinQueueHandlerTest extends KernelTestCase
{
    #[Test]
    public function itShouldCallSupportedHandlers(): void
    {
        $supportedHandler = $this->createMock(JoinQueueHandlerInterface::class);
        $supportedHandler->expects($this->once())
            ->method('supports')
            ->with($context = $this->createStub(JoinQueueContext::class))
            ->willReturn(true);

        $supportedHandler->expects($this->once())
            ->method('handle')
            ->with($context);

        $unsupportedHandler = $this->createMock(JoinQueueHandlerInterface::class);
        $unsupportedHandler->expects($this->once())
            ->method('supports')
            ->with($context)
            ->willReturn(false);

        $unsupportedHandler->expects($this->never())
            ->method('handle')
            ->with($context);

        $handler = new JoinQueueHandler([$supportedHandler, $unsupportedHandler]);

        $handler->handle($context);
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Queue\Pop;

use App\Service\Queue\Pop\Handler\PopQueueHandlerInterface;
use App\Service\Queue\Pop\PopQueueContext;
use App\Service\Queue\Pop\PopQueueHandler;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(PopQueueHandler::class)]
class PopQueueHandlerTest extends KernelTestCase
{
    #[Test]
    public function itShouldCallSupportedHandlers(): void
    {
        $supportedHandler = $this->createMock(PopQueueHandlerInterface::class);
        $supportedHandler->expects($this->once())
            ->method('supports')
            ->with($context = $this->createStub(PopQueueContext::class))
            ->willReturn(true);

        $supportedHandler->expects($this->once())
            ->method('handle')
            ->with($context);

        $unsupportedHandler = $this->createMock(PopQueueHandlerInterface::class);
        $unsupportedHandler->expects($this->once())
            ->method('supports')
            ->with($context)
            ->willReturn(false);

        $unsupportedHandler->expects($this->never())
            ->method('handle')
            ->with($context);

        $handler = new PopQueueHandler([$supportedHandler, $unsupportedHandler]);

        $handler->handle($context);
    }
}

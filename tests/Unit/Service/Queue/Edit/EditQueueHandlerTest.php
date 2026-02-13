<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Queue\Edit;

use App\Service\Queue\Edit\EditQueueContext;
use App\Service\Queue\Edit\EditQueueHandler;
use App\Service\Queue\Edit\Handler\EditQueueHandlerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(EditQueueHandler::class)]
class EditQueueHandlerTest extends KernelTestCase
{
    #[Test]
    public function itShouldCallHandleOnHandlersThatSupportTheContext(): void
    {
        $context = $this->createStub(EditQueueContext::class);

        $supportedHandler = $this->createMock(EditQueueHandlerInterface::class);
        $supportedHandler->expects($this->once())
            ->method('supports')
            ->with($context)
            ->willReturn(true);

        $supportedHandler->expects($this->once())
            ->method('handle')
            ->with($context);

        $unsupportedHandler = $this->createMock(EditQueueHandlerInterface::class);
        $unsupportedHandler->expects($this->once())
            ->method('supports')
            ->with($context)
            ->willReturn(false);

        $unsupportedHandler->expects($this->never())
            ->method('handle')
            ->with($context);

        $handler = new EditQueueHandler([$supportedHandler, $unsupportedHandler]);

        $handler->handle($context);
    }
}

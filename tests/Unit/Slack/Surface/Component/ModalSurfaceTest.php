<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Surface\Component;

use App\Slack\Block\Component\SlackBlock;
use App\Slack\Surface\Component\ModalSurface;
use App\Tests\Unit\Slack\WithSurfaceAssertions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(ModalSurface::class)]
class ModalSurfaceTest extends KernelTestCase
{
    use WithSurfaceAssertions;

    #[Test]
    public function itShouldMapCorrectlyToArray(): void
    {
        $block = $this->createMock(SlackBlock::class);
        $block->expects($this->once())
            ->method('toArray')
            ->willReturn($blockValue = ['block']);

        $modal = new ModalSurface(
            $triggerId = 'triggerId',
            $title = 'title',
            $blocks = [$block],
            $close = 'close',
            $submit = null,
            $privateMetadata = 'privateMetadata',
            $callbackId = null,
            $notifyOnClose = false,
            $clearOnClose = true,
        );

        $this->assertModalSurfaceCorrectlyFormed(
            $modal->toArray(),
            $triggerId,
            $title,
            [$blockValue],
            $close,
            $submit,
            $privateMetadata,
            $callbackId,
            $notifyOnClose,
            $clearOnClose
        );
    }
}

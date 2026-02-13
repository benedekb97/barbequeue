<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Block\Component;

use App\Slack\Block\Block;
use App\Slack\Block\Component\DividerBlock;
use App\Tests\Unit\Slack\WithBlockAssertions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(DividerBlock::class)]
class DividerBlockTest extends KernelTestCase
{
    use WithBlockAssertions;

    #[Test]
    public function itShouldReturnDividerBlockType(): void
    {
        $block = new DividerBlock();

        $this->assertEquals(Block::DIVIDER, $block->getType());
    }

    #[Test]
    public function itShouldMapCorrectlyToArray(): void
    {
        $block = new DividerBlock();

        $this->assertDividerBlockCorrectlyFormatted($block->toArray());
    }
}

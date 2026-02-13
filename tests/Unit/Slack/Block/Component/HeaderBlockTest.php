<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Block\Component;

use App\Slack\Block\Block;
use App\Slack\Block\Component\HeaderBlock;
use App\Tests\Unit\Slack\WithBlockAssertions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(HeaderBlock::class)]
class HeaderBlockTest extends KernelTestCase
{
    use WithBlockAssertions;

    #[Test]
    public function itShouldReturnCorrectType(): void
    {
        $block = new HeaderBlock('text');

        $this->assertEquals(Block::HEADER, $block->getType());
    }

    #[Test]
    public function itShouldMapCorrectlyToArray(): void
    {
        $block = new HeaderBlock($text = 'text');

        $this->assertHeaderBlockCorrectlyFormatted($text, $block->toArray());
    }
}

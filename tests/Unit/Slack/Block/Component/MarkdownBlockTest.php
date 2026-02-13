<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Block\Component;

use App\Slack\Block\Block;
use App\Slack\Block\Component\MarkdownBlock;
use App\Tests\Unit\Slack\WithBlockAssertions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(MarkdownBlock::class)]
class MarkdownBlockTest extends KernelTestCase
{
    use WithBlockAssertions;

    #[Test]
    public function itShouldReturnCorrectType(): void
    {
        $block = new MarkdownBlock('text');

        $this->assertEquals(Block::MARKDOWN, $block->getType());
    }

    #[Test]
    public function itShouldMapCorrectlyToArray(): void
    {
        $block = new MarkdownBlock($text = 'text');

        $this->assertMarkdownBlockCorrectlyFormatted($text, $block->toArray());
    }
}

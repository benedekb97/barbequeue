<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Block\Component;

use App\Slack\Block\Block;
use App\Slack\Block\Component\SectionBlock;
use App\Slack\BlockElement\Component\SlackBlockElement;
use App\Tests\Unit\Slack\WithBlockAssertions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(SectionBlock::class)]
class SectionBlockTest extends KernelTestCase
{
    use WithBlockAssertions;

    #[Test]
    public function itShouldReturnCorrectType(): void
    {
        $block = new SectionBlock('text');

        $this->assertEquals(Block::SECTION, $block->getType());
    }

    #[Test]
    public function itShouldMapCorrectlyToArray(): void
    {
        $accessory = $this->createMock(SlackBlockElement::class);
        $accessory->expects($this->once())
            ->method('toArray')
            ->willReturn($accessoryValue = ['accessory']);

        $block = new SectionBlock(
            $text = 'text',
            $blockId = 'blockId',
            $accessory,
            $expand = true
        );

        $this->assertSectionBlockCorrectlyFormatted($text, $block->toArray(), $blockId, $accessoryValue, $expand);
    }
}

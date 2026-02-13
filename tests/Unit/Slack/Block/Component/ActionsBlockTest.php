<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Block\Component;

use App\Slack\Block\Block;
use App\Slack\Block\Component\ActionsBlock;
use App\Slack\BlockElement\Component\SlackBlockElement;
use App\Tests\Unit\Slack\WithBlockAssertions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(ActionsBlock::class)]
class ActionsBlockTest extends KernelTestCase
{
    use WithBlockAssertions;

    #[Test]
    public function itShouldReturnActionsBlockType(): void
    {
        $block = new ActionsBlock([]);

        $this->assertEquals(Block::ACTIONS, $block->getType());
    }

    #[Test]
    public function itShouldMapElementsToArray(): void
    {
        $firstElement = $this->createMock(SlackBlockElement::class);
        $firstElement->expects($this->once())
            ->method('toArray')
            ->willReturn($firstElementValue = ['blockElement1']);

        $secondElement = $this->createMock(SlackBlockElement::class);
        $secondElement->expects($this->once())
            ->method('toArray')
            ->willReturn($secondElementValue = ['blockElement2']);

        $block = new ActionsBlock([
            $firstElement,
            $secondElement,
        ], $blockId = 'blockId');

        $result = $block->toArray();
        $this->assertActionsBlockCorrectlyFormatted([
            $firstElementValue,
            $secondElementValue,
        ], $result, $blockId);
    }
}

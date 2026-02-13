<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Response\Interaction;

use App\Slack\Block\Component\SlackBlock;
use App\Slack\Response\Interaction\SlackInteractionResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(SlackInteractionResponse::class)]
class SlackInteractionResponseTest extends KernelTestCase
{
    #[Test]
    public function itShouldMapCorrectlyToArray(): void
    {
        $block = $this->createMock(SlackBlock::class);
        $block->expects($this->once())
            ->method('toArray')
            ->willReturn($blockValue = ['block']);

        $response = new SlackInteractionResponse([$block]);

        $result = $response->toArray();

        $this->assertArrayHasKey('blocks', $result);
        $this->assertIsArray($blocks = $result['blocks']);
        $this->assertCount(1, $blocks);
        $this->assertEquals($blockValue, $blocks[0]);
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Message\Component;

use App\Slack\Block\Component\SlackBlock;
use App\Slack\Message\Component\SlackMessage;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(SlackMessage::class)]
class SlackMessageTest extends KernelTestCase
{
    #[Test]
    public function itShouldMapCorrectlyToArray(): void
    {
        $block = $this->createMock(SlackBlock::class);
        $block->expects($this->once())
            ->method('toArray')
            ->willReturn($blockValue = ['block']);

        $message = new SlackMessage(
            $text = 'text',
            [$block],
        );

        $result = $message->toArray();

        $this->assertArrayHasKey('text', $result);
        $this->assertEquals($text, $result['text']);
        $this->assertArrayHasKey('blocks', $result);
        $this->assertIsArray($result['blocks']);
        $this->assertCount(1, $result['blocks']);
        $this->assertEquals($blockValue, $result['blocks'][0]);
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Response\Command;

use App\Slack\Block\Component\SlackBlock;
use App\Slack\Response\Command\SlackCommandResponse;
use App\Slack\Response\Response;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(SlackCommandResponse::class)]
class SlackCommandResponseTest extends KernelTestCase
{
    #[Test]
    public function itShouldMapCorrectlyToArray(): void
    {
        $block = $this->createMock(SlackBlock::class);
        $block->expects($this->once())
            ->method('toArray')
            ->willReturn($blockValue = ['block']);

        $response = new SlackCommandResponse(
            $type = Response::IN_CHANNEL,
            $text = 'text',
            [$block],
        );

        $result = $response->toArray();

        $this->assertArrayHasKey('response_type', $result);
        $this->assertEquals($type->value, $result['response_type']);

        $this->assertArrayHasKey('text', $result);
        $this->assertEquals($text, $result['text']);

        $this->assertArrayHasKey('blocks', $result);
        $this->assertIsArray($resultBlocks = $result['blocks']);
        $this->assertCount(1, $resultBlocks);
        $this->assertEquals($blockValue, $resultBlocks[0]);
    }
}

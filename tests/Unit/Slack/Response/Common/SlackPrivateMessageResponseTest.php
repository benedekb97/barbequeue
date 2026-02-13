<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Response\Common;

use App\Entity\User;
use App\Entity\Workspace;
use App\Slack\Block\Component\SlackBlock;
use App\Slack\Response\PrivateMessage\SlackPrivateMessage;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(SlackPrivateMessage::class)]
class SlackPrivateMessageResponseTest extends KernelTestCase
{
    #[Test]
    public function itShouldReturnPropertiesPassed(): void
    {
        $block = $this->createMock(SlackBlock::class);
        $block->expects($this->once())
            ->method('toArray')
            ->willReturn($blockValue = ['block']);

        $response = new SlackPrivateMessage(
            $user = $this->createStub(User::class),
            $workspace = $this->createStub(Workspace::class),
            $text = 'text',
            [$block],
        );

        $this->assertEquals($user, $response->getUser());
        $this->assertEquals($workspace, $response->getWorkspace());

        $response = $response->toArray();

        $this->assertArrayHasKey('blocks', $response);
        $this->assertIsString($blocks = $response['blocks']);

        $blocks = json_decode($blocks, true);

        $this->assertIsArray($blocks);
        $this->assertCount(1, $blocks);
        $this->assertEquals($blockValue, $blocks[0]);

        $this->assertArrayHasKey('text', $response);
        $this->assertEquals($text, $response['text']);
    }
}

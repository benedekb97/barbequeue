<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Surface\Component;

use App\Entity\Workspace;
use App\Slack\Block\Component\SlackBlock;
use App\Slack\Surface\Component\HomeSurface;
use App\Slack\Surface\Surface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(HomeSurface::class)]
class HomeSurfaceTest extends KernelTestCase
{
    #[Test]
    public function itShouldMapCorrectlyToArray(): void
    {
        $block = $this->createMock(SlackBlock::class);
        $block->expects($this->once())
            ->method('toArray')
            ->willReturn([]);

        $surface = new HomeSurface(
            $userId = 'userId',
            $workspace = $this->createStub(Workspace::class),
            [$block],
        );

        $this->assertSame($workspace, $surface->getWorkspace());
        $this->assertEquals(Surface::HOME, $surface->getType());

        $result = $surface->toArray();

        $this->assertArrayHasKey('user_id', $result);
        $this->assertEquals($userId, $result['user_id']);

        $this->assertArrayHasKey('view', $result);
        $this->assertIsString($view = $result['view']);

        $view = json_decode($view, true);

        $this->assertIsArray($view);

        $this->assertArrayHasKey('type', $view);
        $this->assertEquals('home', $view['type']);

        $this->assertArrayHasKey('blocks', $view);
        $this->assertIsArray($blocks = $view['blocks']);
        $this->assertCount(1, $blocks);
        $this->assertEquals([], $blocks[0]);
    }
}

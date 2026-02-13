<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Block\Component\Table;

use App\Slack\Block\Component\Table\LinkCell;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(LinkCell::class)]
class LinkCellTest extends KernelTestCase
{
    #[Test]
    public function itShouldMapCorrectlyToArray(): void
    {
        $cell = new LinkCell($url = 'url', $text = 'text');

        $result = $cell->toArray();

        $this->assertArrayHasKey('type', $result);
        $this->assertEquals('rich_text', $result['type']);

        $this->assertArrayHasKey('elements', $result);
        $this->assertIsArray($elements = $result['elements']);
        $this->assertCount(1, $elements);

        $this->assertIsArray($section = $elements[0]);
        $this->assertArrayHasKey('type', $section);
        $this->assertEquals('rich_text_section', $section['type']);

        $this->assertArrayHasKey('elements', $section);
        $this->assertIsArray($elements = $section['elements']);

        $this->assertCount(1, $elements);
        $this->assertIsArray($result = $elements[0]);
        $this->assertArrayHasKey('type', $result);
        $this->assertEquals('link', $result['type']);

        $this->assertArrayHasKey('text', $result);
        $this->assertEquals($text, $result['text']);

        $this->assertArrayHasKey('url', $result);
        $this->assertEquals($url, $result['url']);
    }
}

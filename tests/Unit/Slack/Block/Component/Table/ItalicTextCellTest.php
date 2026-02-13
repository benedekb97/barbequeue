<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Block\Component\Table;

use App\Slack\Block\Component\Table\ItalicTextCell;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(ItalicTextCell::class)]
class ItalicTextCellTest extends KernelTestCase
{
    #[Test]
    public function itShouldMapCorrectlyToArray(): void
    {
        $cell = new ItalicTextCell($text = 'text');

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
        $this->assertEquals('text', $result['type']);
        $this->assertArrayHasKey('text', $result);
        $this->assertEquals($text, $result['text']);

        $this->assertArrayHasKey('style', $result);
        $this->assertIsArray($result['style']);
        $this->assertArrayHasKey('italic', $result['style']);
        $this->assertTrue($result['style']['italic']);
    }
}

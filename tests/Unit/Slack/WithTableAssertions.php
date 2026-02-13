<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/** @mixin KernelTestCase */
trait WithTableAssertions
{
    public function getTableRow(mixed $table, int $index): array
    {
        $this->assertIsArray($table);
        $this->assertArrayHasKey($index, $table);
        $this->assertIsArray($table[$index]);

        return $table[$index];
    }

    public function getRowCell(mixed $row, int $index): array
    {
        $this->assertIsArray($row);
        $this->assertArrayHasKey($index, $row);
        $this->assertIsArray($row[$index]);

        return $row[$index];
    }

    public function assertTableRowCount(mixed $table, int $count): array
    {
        $this->assertIsArray($table);

        $this->assertArrayHasKey('type', $table);
        $this->assertEquals('table', $table['type']);
        $this->assertArrayHasKey('rows', $table);
        $this->assertIsArray($table['rows']);
        $this->assertCount($count, $table['rows']);

        return $table['rows'];
    }

    public function assertRawTextCell(mixed $cell, string $text): void
    {
        $this->assertIsArray($cell);
        $this->assertArrayHasKey('type', $cell);
        $this->assertEquals('raw_text', $cell['type']);
        $this->assertArrayHasKey('text', $cell);
        $this->assertEquals($text, $cell['text']);
    }

    public function assertRichText(mixed $cell): array
    {
        $this->assertIsArray($cell);

        $this->assertArrayHasKey('type', $cell);
        $this->assertEquals('rich_text', $cell['type']);

        $this->assertArrayHasKey('elements', $cell);
        $this->assertIsArray($elements = $cell['elements']);
        $this->assertCount(1, $elements);

        $this->assertIsArray($section = $elements[0]);
        $this->assertArrayHasKey('type', $section);
        $this->assertEquals('rich_text_section', $section['type']);

        $this->assertArrayHasKey('elements', $section);
        $this->assertIsArray($elements = $section['elements']);

        $this->assertCount(1, $elements);
        $this->assertIsArray($cell = $elements[0]);

        return $cell;
    }

    public function assertItalicTextCell(mixed $cell, string $text): void
    {
        $cell = $this->assertRichText($cell);

        $this->assertArrayHasKey('type', $cell);
        $this->assertEquals('text', $cell['type']);
        $this->assertArrayHasKey('text', $cell);
        $this->assertEquals($text, $cell['text']);

        $this->assertArrayHasKey('style', $cell);
        $this->assertIsArray($cell['style']);
        $this->assertArrayHasKey('italic', $cell['style']);
        $this->assertTrue($cell['style']['italic']);
    }

    public function assertLinkCell(mixed $cell, string $url, ?string $text = null): void
    {
        $text ??= $url;

        $cell = $this->assertRichText($cell);

        $this->assertArrayHasKey('type', $cell);
        $this->assertEquals('link', $cell['type']);

        $this->assertArrayHasKey('url', $cell);
        $this->assertEquals($url, $cell['url']);

        $this->assertArrayHasKey('text', $cell);
        $this->assertEquals($text, $cell['text']);
    }

    public function assertUserCell(mixed $cell, string $userId): void
    {
        $cell = $this->assertRichText($cell);

        $this->assertArrayHasKey('type', $cell);
        $this->assertEquals('user', $cell['type']);

        $this->assertArrayHasKey('user_id', $cell);
        $this->assertEquals($userId, $cell['user_id']);
    }
}

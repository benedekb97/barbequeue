<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/** @mixin KernelTestCase */
trait WithBlockAssertions
{
    protected function assertHeaderBlockCorrectlyFormatted(string $expectedText, mixed $actual): void
    {
        $this->assertIsArray($actual);
        $this->assertArrayHasKey('type', $actual);
        $this->assertEquals('header', $actual['type']);
        $this->assertArrayHasKey('text', $actual);
        $this->assertIsArray($text = $actual['text']);
        $this->assertArrayHasKey('type', $text);
        $this->assertEquals('plain_text', $text['type']);
        $this->assertArrayHasKey('text', $text);
        $this->assertEquals($expectedText, $text['text']);
        $this->assertArrayHasKey('emoji', $text);
        $this->assertTrue($text['emoji']);
    }

    protected function assertSectionBlockCorrectlyFormatted(
        string $expectedText,
        mixed $actual,
        ?string $blockId = null,
        ?array $expectedAccessory = null,
        bool $expectedExpand = false,
    ): void {
        $this->assertIsArray($actual);
        $this->assertArrayHasKey('type', $actual);
        $this->assertEquals('section', $actual['type']);
        $this->assertArrayHasKey('text', $actual);
        $this->assertIsArray($text = $actual['text']);
        $this->assertArrayHasKey('type', $text);
        $this->assertEquals('mrkdwn', $text['type']);
        $this->assertArrayHasKey('text', $text);
        $this->assertEquals($expectedText, $text['text']);

        if (null !== $blockId) {
            $this->assertArrayHasKey('block_id', $actual);
            $this->assertEquals($blockId, $actual['block_id']);
        } else {
            $this->assertArrayNotHasKey('block_id', $actual);
        }

        if (null !== $expectedAccessory) {
            $this->assertArrayHasKey('accessory', $actual);
            $this->assertIsArray($accessory = $actual['accessory']);
            $this->assertEquals($expectedAccessory, $accessory);
        } else {
            $this->assertArrayNotHasKey('accessory', $actual);
        }

        if ($expectedExpand) {
            $this->assertArrayHasKey('expand', $actual);
            $this->assertTrue($actual['expand']);
        } else {
            $this->assertArrayNotHasKey('expand', $actual);
        }
    }

    protected function assertDividerBlockCorrectlyFormatted(mixed $actual): void
    {
        $this->assertIsArray($actual);
        $this->assertArrayHasKey('type', $actual);
        $this->assertEquals('divider', $actual['type']);
    }

    protected function assertTableBlockCorrectlyFormatted(
        array $expectedRows,
        mixed $actual,
        ?string $expectedBlockId = null,
        ?array $expectedColumnSettings = null,
    ): void {
        $this->assertIsArray($actual);
        $this->assertArrayHasKey('type', $actual);
        $this->assertEquals('table', $actual['type']);
        $this->assertArrayHasKey('rows', $actual);
        $this->assertIsArray($actualRows = $actual['rows']);
        $this->assertEquals($expectedRows, $actualRows);

        if (null !== $expectedColumnSettings) {
            $this->assertArrayHasKey('column_settings', $actual);
            $this->assertIsArray($columnSettings = $actual['column_settings']);
            $this->assertEquals($expectedColumnSettings, $columnSettings);
        } else {
            $this->assertArrayNotHasKey('column_settings', $actual);
        }

        if (null !== $expectedBlockId) {
            $this->assertArrayHasKey('block_id', $actual);
            $this->assertEquals($expectedBlockId, $actual['block_id']);
        } else {
            $this->assertArrayNotHasKey('block_id', $actual);
        }
    }

    protected function assertActionsBlockCorrectlyFormatted(
        array $expectedElements,
        mixed $actual,
        ?string $expectedBlockId = null,
        bool $ignoreElements = false,
    ): void {
        $this->assertIsArray($actual);
        $this->assertArrayHasKey('type', $actual);
        $this->assertEquals('actions', $actual['type']);
        $this->assertArrayHasKey('elements', $actual);
        $this->assertIsArray($elements = $actual['elements']);

        if (!$ignoreElements) {
            $this->assertEquals($expectedElements, $elements);
        }

        if (null === $expectedBlockId) {
            return;
        }

        $this->assertArrayHasKey('block_id', $actual);
        $this->assertEquals($expectedBlockId, $actual['block_id']);
    }

    protected function assertInputBlockCorrectlyFormatted(
        mixed $actual,
        string $expectedLabel,
        array $expectedElement = [],
        bool $expectedDispatchAction = false,
        ?string $expectedBlockId = null,
        ?string $expectedHint = null,
        bool $expectedOptional = false,
        bool $ignoreElement = false,
    ): void {
        $this->assertIsArray($actual);
        $this->assertArrayHasKey('type', $actual);
        $this->assertEquals('input', $actual['type']);

        $this->assertArrayHasKey('label', $actual);
        $this->assertIsArray($label = $actual['label']);
        $this->assertArrayHasKey('text', $label);
        $this->assertEquals($expectedLabel, $label['text']);
        $this->assertArrayHasKey('type', $label);
        $this->assertEquals('plain_text', $label['type']);

        if (!$ignoreElement) {
            $this->assertArrayHasKey('element', $actual);
            $this->assertIsArray($element = $actual['element']);
            $this->assertEquals($expectedElement, $element);
        }

        if ($expectedDispatchAction) {
            $this->assertArrayHasKey('dispatch_action', $actual);
            $this->assertEquals($expectedDispatchAction, $actual['dispatch_action']);
        } else {
            $this->assertArrayNotHasKey('dispatch_action', $actual);
        }

        if ($expectedOptional) {
            $this->assertArrayHasKey('optional', $actual);
            $this->assertEquals($expectedOptional, $actual['optional']);
        } else {
            $this->assertArrayNotHasKey('optional', $actual);
        }

        if (null !== $expectedBlockId) {
            $this->assertArrayHasKey('block_id', $actual);
            $this->assertEquals($expectedBlockId, $actual['block_id']);
        } else {
            $this->assertArrayNotHasKey('block_id', $actual);
        }

        if (null !== $expectedHint) {
            $this->assertArrayHasKey('hint', $actual);
            $this->assertIsArray($hint = $actual['hint']);
            $this->assertArrayHasKey('text', $hint);
            $this->assertEquals($expectedHint, $hint['text']);
            $this->assertArrayHasKey('type', $hint);
            $this->assertEquals('plain_text', $hint['type']);
        } else {
            $this->assertArrayNotHasKey('hint', $actual);
        }
    }

    protected function assertMarkdownBlockCorrectlyFormatted(string $expectedText, mixed $actual): void
    {
        $this->assertIsArray($actual);
        $this->assertArrayHasKey('type', $actual);
        $this->assertEquals('markdown', $actual['type']);
        $this->assertArrayHasKey('text', $actual);
        $this->assertEquals($expectedText, $actual['text']);
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/** @mixin KernelTestCase */
trait WithBlockAssertions
{
    protected function assertSectionBlockCorrectlyFormatted(string $expectedText, mixed $actual): void
    {
        $this->assertIsArray($actual);
        $this->assertArrayHasKey('type', $actual);
        $this->assertEquals('section', $actual['type']);
        $this->assertArrayHasKey('text', $actual);
        $this->assertIsArray($text = $actual['text']);
        $this->assertArrayHasKey('type', $text);
        $this->assertEquals('mrkdwn', $text['type']);
        $this->assertArrayHasKey('text', $text);
        $this->assertEquals($expectedText, $text['text']);
    }

    protected function assertDividerBlockCorrectlyFormatted(mixed $actual): void
    {
        $this->assertIsArray($actual);
        $this->assertArrayHasKey('type', $actual);
        $this->assertEquals('divider', $actual['type']);
    }

    protected function assertTableBlockCorrectlyFormatted(array $expectedRows, mixed $actual): void
    {
        $this->assertIsArray($actual);
        $this->assertArrayHasKey('type', $actual);
        $this->assertEquals('table', $actual['type']);
        $this->assertArrayHasKey('rows', $actual);
        $this->assertIsArray($actualRows = $actual['rows']);
        $this->assertEquals($expectedRows, $actualRows);
    }

    protected function assertActionsBlockCorrectlyFormatted(array $expectedElements, mixed $actual): void
    {
        $this->assertIsArray($actual);
        $this->assertArrayHasKey('type', $actual);
        $this->assertEquals('actions', $actual['type']);
        $this->assertArrayHasKey('elements', $actual);
        $this->assertIsArray($elements = $actual['elements']);
        $this->assertEquals($expectedElements, $elements);
    }
}

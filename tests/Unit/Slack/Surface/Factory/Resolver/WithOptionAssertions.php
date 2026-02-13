<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Surface\Factory\Resolver;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/** @mixin KernelTestCase */
trait WithOptionAssertions
{
    private function assertOptionFormedCorrectly(mixed $actual, string $value, string $text): void
    {
        $this->assertIsArray($actual);
        $this->assertArrayHasKey('text', $actual);
        $this->assertArrayHasKey('value', $actual);
        $this->assertIsArray($actual['text']);
        $this->assertArrayHasKey('text', $actual['text']);
        $this->assertEquals($text, $actual['text']['text']);
        $this->assertArrayHasKey('type', $actual['text']);
        $this->assertEquals('plain_text', $actual['text']['type']);
        $this->assertEquals($value, $actual['value']);
    }
}

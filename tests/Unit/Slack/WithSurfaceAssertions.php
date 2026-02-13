<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/** @mixin KernelTestCase */
trait WithSurfaceAssertions
{
    private function assertModalSurfaceCorrectlyFormed(
        mixed $actual,
        string $expectedTriggerId,
        string $expectedTitle,
        ?array $expectedBlocks = null,
        ?string $expectedClose = null,
        ?string $expectedSubmit = null,
        ?string $expectedPrivateMetadata = null,
        ?string $expectedCallbackId = null,
        bool $expectedNotifyOnClose = false,
        bool $expectedClearOnClose = false,
        bool $ignoreBlocks = false,
    ): void {
        $this->assertIsArray($actual);

        $this->assertArrayHasKey('trigger_id', $actual);
        $this->assertEquals($expectedTriggerId, $actual['trigger_id']);

        $this->assertArrayHasKey('view', $actual);
        $this->assertIsString($view = $actual['view']);

        $view = json_decode($view, true);

        $this->assertIsArray($view);

        $this->assertArrayHasKey('type', $view);
        $this->assertEquals('modal', $view['type']);

        $this->assertArrayHasKey('title', $view);
        $this->assertIsArray($title = $view['title']);
        $this->assertArrayHasKey('type', $title);
        $this->assertEquals('plain_text', $title['type']);
        $this->assertArrayHasKey('text', $title);
        $this->assertEquals($expectedTitle, $title['text']);

        $this->assertArrayHasKey('blocks', $view);
        $this->assertIsArray($view['blocks']);

        if (!$ignoreBlocks) {
            $this->assertEquals($expectedBlocks, $view['blocks']);
        }

        if (null !== $expectedClose) {
            $this->assertArrayHasKey('close', $view);
            $this->assertIsArray($close = $view['close']);
            $this->assertArrayHasKey('type', $close);
            $this->assertEquals('plain_text', $close['type']);
            $this->assertArrayHasKey('text', $close);
            $this->assertEquals($expectedClose, $close['text']);
        } else {
            $this->assertArrayNotHasKey('close', $view);
        }

        if (null !== $expectedSubmit) {
            $this->assertArrayHasKey('submit', $view);
            $this->assertIsArray($submit = $view['submit']);
            $this->assertArrayHasKey('type', $submit);
            $this->assertEquals('plain_text', $submit['type']);
            $this->assertArrayHasKey('text', $submit);
            $this->assertEquals($expectedSubmit, $submit['text']);
        } else {
            $this->assertArrayNotHasKey('submit', $view);
        }

        if (null !== $expectedPrivateMetadata) {
            $this->assertArrayHasKey('private_metadata', $view);
            $this->assertEquals($expectedPrivateMetadata, $view['private_metadata']);
        } else {
            $this->assertArrayNotHasKey('private_metadata', $view);
        }

        if (null !== $expectedCallbackId) {
            $this->assertArrayHasKey('callback_id', $view);
            $this->assertEquals($expectedCallbackId, $view['callback_id']);
        } else {
            $this->assertArrayNotHasKey('callback_id', $view);
        }

        if ($expectedClearOnClose) {
            $this->assertArrayHasKey('clear_on_close', $view);
            $this->assertEquals($expectedClearOnClose, $view['clear_on_close']);
        } else {
            $this->assertArrayNotHasKey('clear_on_close', $view);
        }

        if ($expectedNotifyOnClose) {
            $this->assertArrayHasKey('notify_on_close', $view);
            $this->assertEquals($expectedNotifyOnClose, $view['notify_on_close']);
        } else {
            $this->assertArrayNotHasKey('notify_on_close', $view);
        }

        $this->assertArrayNotHasKey('external_id', $view);
    }

    private function assertHomeSurfaceCorrectlyFormed(
        mixed $actual,
        string $expectedUserId,
    ): array {
        $this->assertIsArray($actual);
        $this->assertArrayHasKey('user_id', $actual);
        $this->assertEquals($expectedUserId, $actual['user_id']);

        $this->assertArrayHasKey('view', $actual);
        $this->assertIsString($view = $actual['view']);

        $view = json_decode($view, true);

        $this->assertIsArray($view);
        $this->assertArrayHasKey('type', $view);
        $this->assertEquals('home', $view['type']);

        return $view;
    }
}

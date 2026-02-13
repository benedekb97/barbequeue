<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack;

use App\Slack\Common\Style;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/** @mixin KernelTestCase */
trait WithBlockElementAssertions
{
    protected function assertButtonBlockElementCorrectlyFormatted(
        string $expectedText,
        mixed $actual,
        ?string $expectedActionId = null,
        ?string $expectedUrl = null,
        ?string $expectedValue = null,
        ?Style $expectedStyle = null,
        ?array $expectedConfirm = null,
    ): void {
        $this->assertIsArray($actual);
        $this->assertArrayHasKey('type', $actual);
        $this->assertEquals('button', $actual['type']);

        $this->assertArrayHasKey('text', $actual);
        $this->assertIsArray($text = $actual['text']);
        $this->assertArrayHasKey('type', $text);
        $this->assertEquals('plain_text', $text['type']);
        $this->assertArrayHasKey('text', $text);
        $this->assertEquals($expectedText, $text['text']);

        if (null !== $expectedActionId) {
            $this->assertArrayHasKey('action_id', $actual);
            $this->assertEquals($expectedActionId, $actual['action_id']);
        } else {
            $this->assertArrayNotHasKey('action_id', $actual);
        }

        if (null !== $expectedUrl) {
            $this->assertArrayHasKey('url', $actual);
            $this->assertEquals($expectedUrl, $actual['url']);
        } else {
            $this->assertArrayNotHasKey('url', $actual);
        }

        if (null !== $expectedValue) {
            $this->assertArrayHasKey('value', $actual);
            $this->assertEquals($expectedValue, $actual['value']);
        } else {
            $this->assertArrayNotHasKey('value', $actual);
        }

        if (null !== $expectedStyle) {
            $this->assertArrayHasKey('style', $actual);
            $this->assertEquals($expectedStyle->value, $actual['style']);
        } else {
            $this->assertArrayNotHasKey('style', $actual);
        }

        if (null !== $expectedConfirm) {
            $this->assertArrayHasKey('confirm', $actual);
            $this->assertIsArray($confirm = $actual['confirm']);
            $this->assertEquals($expectedConfirm, $confirm);
        }
    }

    protected function assertEmailBlockElementCorrectlyFormatted(
        mixed $actual,
        ?string $expectedActionId = null,
        ?string $expectedInitialValue = null,
        bool $expectedFocusOnLoad = false,
        ?string $expectedPlaceholder = null,
    ): void {
        $this->assertIsArray($actual);
        $this->assertArrayHasKey('type', $actual);
        $this->assertEquals('email_text_input', $actual['type']);

        $this->assertTextElementCorrectlyFormatted(
            $actual,
            $expectedActionId,
            $expectedInitialValue,
            $expectedFocusOnLoad,
            $expectedPlaceholder,
        );
    }

    protected function assertNumberBlockElementCorrectlyFormatted(
        bool $expectedIsDecimalAllowed,
        mixed $actual,
        ?float $expectedMinValue = null,
        ?float $expectedMaxValue = null,
        ?string $expectedActionId = null,
        ?string $expectedInitialValue = null,
        bool $expectedFocusOnLoad = false,
        ?string $expectedPlaceholder = null,
    ): void {
        $this->assertIsArray($actual);

        $this->assertArrayHasKey('type', $actual);
        $this->assertEquals('number_input', $actual['type']);

        $this->assertArrayHasKey('is_decimal_allowed', $actual);
        $this->assertEquals($expectedIsDecimalAllowed, $actual['is_decimal_allowed']);

        if (null !== $expectedMinValue) {
            $this->assertArrayHasKey('min_value', $actual);
            $this->assertEquals($expectedMinValue, $actual['min_value']);
        } else {
            $this->assertArrayNotHasKey('min_value', $actual);
        }

        if (null !== $expectedMaxValue) {
            $this->assertArrayHasKey('max_value', $actual);
            $this->assertEquals($expectedMaxValue, $actual['max_value']);
        } else {
            $this->assertArrayNotHasKey('max_value', $actual);
        }

        $this->assertTextElementCorrectlyFormatted(
            $actual,
            $expectedActionId,
            $expectedInitialValue,
            $expectedFocusOnLoad,
            $expectedPlaceholder,
        );
    }

    protected function assertPlainTextBlockElementCorrectlyFormatted(
        mixed $actual,
        bool $expectedMultiline = false,
        ?int $expectedMinLength = null,
        ?int $expectedMaxLength = null,
        ?string $expectedActionId = null,
        ?string $expectedInitialValue = null,
        bool $expectedFocusOnLoad = false,
        ?string $expectedPlaceholder = null,
    ): void {
        $this->assertIsArray($actual);

        $this->assertArrayHasKey('type', $actual);
        $this->assertEquals('plain_text_input', $actual['type']);

        $this->assertArrayHasKey('multiline', $actual);
        $this->assertEquals($expectedMultiline, $actual['multiline']);

        if (null !== $expectedMinLength) {
            $this->assertArrayHasKey('min_length', $actual);
            $this->assertEquals((string) $expectedMinLength, $actual['min_length']);
        } else {
            $this->assertArrayNotHasKey('min_length', $actual);
        }

        if (null !== $expectedMaxLength) {
            $this->assertArrayHasKey('max_length', $actual);
            $this->assertEquals((string) $expectedMaxLength, $actual['max_length']);
        } else {
            $this->assertArrayNotHasKey('max_length', $actual);
        }

        $this->assertTextElementCorrectlyFormatted(
            $actual,
            $expectedActionId,
            $expectedInitialValue,
            $expectedFocusOnLoad,
            $expectedPlaceholder,
        );
    }

    private function assertTextElementCorrectlyFormatted(
        mixed $actual,
        ?string $expectedActionId = null,
        ?string $expectedInitialValue = null,
        bool $expectedFocusOnLoad = false,
        ?string $expectedPlaceholder = null,
    ): void {
        $this->assertIsArray($actual);

        $this->assertArrayHasKey('focus_on_load', $actual);
        $this->assertEquals($expectedFocusOnLoad, $actual['focus_on_load']);

        if (null !== $expectedActionId) {
            $this->assertArrayHasKey('action_id', $actual);
            $this->assertEquals($expectedActionId, $actual['action_id']);
        } else {
            $this->assertArrayNotHasKey('action_id', $actual);
        }

        if (null !== $expectedInitialValue) {
            $this->assertArrayHasKey('initial_value', $actual);
            $this->assertEquals($expectedInitialValue, $actual['initial_value']);
        } else {
            $this->assertArrayNotHasKey('initial_value', $actual);
        }

        $this->assertArrayHasKey('placeholder', $actual);
        $this->assertIsArray($placeholder = $actual['placeholder']);

        $this->assertArrayHasKey('type', $placeholder);
        $this->assertEquals('plain_text', $placeholder['type']);

        $this->assertArrayHasKey('text', $placeholder);
        $this->assertEquals($expectedPlaceholder, $placeholder['text']);

        $this->assertArrayHasKey('emoji', $placeholder);
        $this->assertFalse($placeholder['emoji']);
    }

    private function assertMultiStaticSelectElementCorrectlyFormatted(
        mixed $actual,
        ?string $expectedActionId = null,
        ?string $expectedPlaceholder = null,
        bool $expectedFocusOnLoad = false,
        array $expectedOptions = [],
        array $expectedInitialOptions = [],
    ): void {
        $this->assertIsArray($actual);

        if ($expectedFocusOnLoad) {
            $this->assertArrayHasKey('focus_on_load', $actual);
            $this->assertEquals($expectedFocusOnLoad, $actual['focus_on_load']);
        }

        if (null !== $expectedActionId) {
            $this->assertArrayHasKey('action_id', $actual);
            $this->assertEquals($expectedActionId, $actual['action_id']);
        } else {
            $this->assertArrayNotHasKey('action_id', $actual);
        }

        if (!empty($expectedOptions)) {
            $this->assertArrayHasKey('options', $actual);
            $this->assertEquals($expectedOptions, $actual['options']);
        } else {
            $this->assertArrayNotHasKey('options', $actual);
        }

        if (!empty($expectedInitialOptions)) {
            $this->assertArrayHasKey('initial_options', $actual);
            $this->assertEquals($expectedInitialOptions, $actual['initial_options']);
        } else {
            $this->assertArrayNotHasKey('initial_options', $actual);
        }

        $this->assertArrayHasKey('type', $actual);
        $this->assertEquals('multi_static_select', $actual['type']);

        $this->assertArrayHasKey('placeholder', $actual);
        $this->assertIsArray($placeholder = $actual['placeholder']);

        $this->assertArrayHasKey('type', $placeholder);
        $this->assertEquals('plain_text', $placeholder['type']);

        $this->assertArrayHasKey('text', $placeholder);
        $this->assertEquals($expectedPlaceholder, $placeholder['text']);
    }

    private function assertMultiUsersSelectElementCorrectlyFormatted(
        mixed $actual,
        ?string $expectedActionId = null,
        ?string $expectedPlaceholder = null,
        bool $expectedFocusOnLoad = false,
    ): void {
        $this->assertIsArray($actual);

        if ($expectedFocusOnLoad) {
            $this->assertArrayHasKey('focus_on_load', $actual);
            $this->assertEquals($expectedFocusOnLoad, $actual['focus_on_load']);
        }

        if (null !== $expectedActionId) {
            $this->assertArrayHasKey('action_id', $actual);
            $this->assertEquals($expectedActionId, $actual['action_id']);
        } else {
            $this->assertArrayNotHasKey('action_id', $actual);
        }

        $this->assertArrayHasKey('type', $actual);
        $this->assertEquals('multi_users_select', $actual['type']);

        $this->assertArrayHasKey('placeholder', $actual);
        $this->assertIsArray($placeholder = $actual['placeholder']);

        $this->assertArrayHasKey('type', $placeholder);
        $this->assertEquals('plain_text', $placeholder['type']);

        $this->assertArrayHasKey('text', $placeholder);
        $this->assertEquals($expectedPlaceholder, $placeholder['text']);
    }

    private function assertCheckboxesElementCorrectlyFormatted(
        mixed $actual,
        bool $expectedFocusOnLoad = false,
        ?string $expectedActionId = null,
    ): void {
        $this->assertIsArray($actual);

        if ($expectedFocusOnLoad) {
            $this->assertArrayHasKey('focus_on_load', $actual);
            $this->assertEquals($expectedFocusOnLoad, $actual['focus_on_load']);
        }

        if (null !== $expectedActionId) {
            $this->assertArrayHasKey('action_id', $actual);
            $this->assertEquals($expectedActionId, $actual['action_id']);
        } else {
            $this->assertArrayNotHasKey('action_id', $actual);
        }

        $this->assertArrayHasKey('type', $actual);
        $this->assertEquals('checkboxes', $actual['type']);
    }

    private function assertStaticSelectElementCorrectlyFormatted(
        mixed $actual,
        ?string $expectedActionId = null,
        ?string $expectedPlaceholder = null,
        bool $expectedFocusOnLoad = false,
        array $expectedOptions = [],
        array $expectedInitialOption = [],
    ): void {
        $this->assertIsArray($actual);

        if ($expectedFocusOnLoad) {
            $this->assertArrayHasKey('focus_on_load', $actual);
            $this->assertEquals($expectedFocusOnLoad, $actual['focus_on_load']);
        }

        if (null !== $expectedActionId) {
            $this->assertArrayHasKey('action_id', $actual);
            $this->assertEquals($expectedActionId, $actual['action_id']);
        } else {
            $this->assertArrayNotHasKey('action_id', $actual);
        }

        if (!empty($expectedOptions)) {
            $this->assertArrayHasKey('options', $actual);
            $this->assertEquals($expectedOptions, $actual['options']);
        } else {
            $this->assertArrayNotHasKey('options', $actual);
        }

        if (!empty($expectedInitialOption)) {
            $this->assertArrayHasKey('initial_option', $actual);
            $this->assertEquals($expectedInitialOption, $actual['initial_option']);
        } else {
            $this->assertArrayNotHasKey('initial_option', $actual);
        }

        $this->assertArrayHasKey('type', $actual);
        $this->assertEquals('static_select', $actual['type']);

        $this->assertArrayHasKey('placeholder', $actual);
        $this->assertIsArray($placeholder = $actual['placeholder']);

        $this->assertArrayHasKey('type', $placeholder);
        $this->assertEquals('plain_text', $placeholder['type']);

        $this->assertArrayHasKey('text', $placeholder);
        $this->assertEquals($expectedPlaceholder, $placeholder['text']);
    }

    private function assertUrlElementCorrectlyFormatted(
        mixed $actual,
        ?string $expectedActionId = null,
        ?string $expectedInitialValue = null,
        bool $expectedFocusOnLoad = false,
        ?string $expectedPlaceholder = null,
    ): void {
        $this->assertIsArray($actual);
        $this->assertArrayHasKey('type', $actual);
        $this->assertEquals('url_text_input', $actual['type']);

        $this->assertTextElementCorrectlyFormatted(
            $actual,
            $expectedActionId,
            $expectedInitialValue,
            $expectedFocusOnLoad,
            $expectedPlaceholder,
        );
    }
}

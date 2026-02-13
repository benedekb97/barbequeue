<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\BlockElement\Component;

use App\Slack\BlockElement\BlockElement;
use App\Slack\BlockElement\Component\PlainTextInputElement;
use App\Tests\Unit\Slack\WithBlockElementAssertions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(PlainTextInputElement::class)]
class PlainTextInputElementTest extends KernelTestCase
{
    use WithBlockElementAssertions;

    #[Test]
    public function itShouldReturnCorrectType(): void
    {
        $element = new PlainTextInputElement();

        $this->assertEquals(BlockElement::PLAIN_TEXT_INPUT, $element->getType());
    }

    #[Test]
    public function itShouldMapCorrectlyToArray(): void
    {
        $element = new PlainTextInputElement(
            $multiline = false,
            $minlength = 1,
            $maxLength = null,
            $actionId = 'actionId',
            $initialValue = null,
            $focusOnLoad = true,
            $placeholder = 'placeholder,'
        );

        $this->assertPlainTextBlockElementCorrectlyFormatted(
            $element->toArray(),
            $multiline,
            $minlength,
            $maxLength,
            $actionId,
            $initialValue,
            $focusOnLoad,
            $placeholder,
        );
    }
}

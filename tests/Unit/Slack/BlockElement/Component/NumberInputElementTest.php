<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\BlockElement\Component;

use App\Slack\BlockElement\BlockElement;
use App\Slack\BlockElement\Component\NumberInputElement;
use App\Tests\Unit\Slack\WithBlockElementAssertions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(NumberInputElement::class)]
class NumberInputElementTest extends KernelTestCase
{
    use WithBlockElementAssertions;

    #[Test]
    public function itShouldReturnCorrectType(): void
    {
        $numberInput = new NumberInputElement(false);

        $this->assertEquals(BlockElement::NUMBER_INPUT, $numberInput->getType());
    }

    #[Test]
    public function itShouldMapCorrectlyToArray(): void
    {
        $numberElement = new NumberInputElement(
            $isDecimalAllowed = false,
            $minValue = 1.2,
            $maxValue = null,
            $actionId = 'actionId',
            $initialValue = 'initialValue',
            $focusOnLoad = true,
            $placeholder = 'placeholder',
        );

        $this->assertNumberBlockElementCorrectlyFormatted(
            $isDecimalAllowed,
            $numberElement->toArray(),
            $minValue,
            $maxValue,
            $actionId,
            $initialValue,
            $focusOnLoad,
            $placeholder,
        );
    }
}

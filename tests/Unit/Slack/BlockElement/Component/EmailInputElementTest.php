<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\BlockElement\Component;

use App\Slack\BlockElement\BlockElement;
use App\Slack\BlockElement\Component\EmailInputElement;
use App\Tests\Unit\Slack\WithBlockElementAssertions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(EmailInputElement::class)]
class EmailInputElementTest extends KernelTestCase
{
    use WithBlockElementAssertions;

    #[Test]
    public function itShouldReturnCorrectType(): void
    {
        $element = new EmailInputElement();

        $this->assertEquals(BlockElement::EMAIL_INPUT, $element->getType());
    }

    #[Test]
    public function itShouldMapCorrectlyToArray(): void
    {
        $element = new EmailInputElement(
            $actionId = 'actionId',
            $initialValue = 'initialValue',
            $focusOnLoad = false,
            $placeholder = 'placeholder',
        );

        $this->assertEmailBlockElementCorrectlyFormatted(
            $element->toArray(),
            $actionId,
            $initialValue,
            $focusOnLoad,
            $placeholder,
        );
    }
}

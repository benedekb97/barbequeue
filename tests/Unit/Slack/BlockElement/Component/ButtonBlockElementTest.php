<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\BlockElement\Component;

use App\Slack\BlockElement\BlockElement;
use App\Slack\BlockElement\Component\ButtonBlockElement;
use App\Slack\Common\Component\SlackConfirmation;
use App\Slack\Common\Style;
use App\Tests\Unit\Slack\WithBlockElementAssertions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(ButtonBlockElement::class)]
class ButtonBlockElementTest extends KernelTestCase
{
    use WithBlockElementAssertions;

    #[Test]
    public function itShouldReturnCorrectType(): void
    {
        $element = new ButtonBlockElement('text');

        $this->assertEquals(BlockElement::BUTTON, $element->getType());
    }

    #[Test]
    public function itShouldMapCorrectlyToArray(): void
    {
        $confirmation = $this->createMock(SlackConfirmation::class);
        $confirmation->expects($this->once())
            ->method('toArray')
            ->willReturn($confirmationValue = ['confirmation']);

        $element = new ButtonBlockElement(
            $text = 'text',
            $actionId = 'actionId',
            $url = 'url',
            $value = 'value',
            $style = Style::PRIMARY,
            $confirmation
        );

        $this->assertButtonBlockElementCorrectlyFormatted(
            $text,
            $element->toArray(),
            $actionId,
            $url,
            $value,
            $style,
            $confirmationValue
        );
    }
}

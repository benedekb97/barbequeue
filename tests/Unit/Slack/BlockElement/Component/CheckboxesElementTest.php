<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\BlockElement\Component;

use App\Slack\BlockElement\Component\CheckboxesElement;
use App\Tests\Unit\Slack\WithBlockElementAssertions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(CheckboxesElement::class)]
class CheckboxesElementTest extends KernelTestCase
{
    use WithBlockElementAssertions;

    #[Test]
    public function itShouldMapCorrectlyToArray(): void
    {
        $element = new CheckboxesElement(
            $actionId = 'actionId',
            $option = ['option'],
            $initialOption = ['option'],
            true,
        );

        $this->assertCheckboxesElementCorrectlyFormatted(
            $result = $element->toArray(),
            true,
            $actionId,
        );

        $this->assertArrayHasKey('options', $result);
        $this->assertEquals($option, $result['options']);

        $this->assertArrayHasKey('initial_options', $result);
        $this->assertEquals($initialOption, $result['initial_options']);
    }
}

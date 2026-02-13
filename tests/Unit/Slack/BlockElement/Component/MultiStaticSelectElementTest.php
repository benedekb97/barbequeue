<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\BlockElement\Component;

use App\Slack\BlockElement\Component\MultiStaticSelectElement;
use App\Tests\Unit\Slack\WithBlockElementAssertions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(MultiStaticSelectElement::class)]
class MultiStaticSelectElementTest extends KernelTestCase
{
    use WithBlockElementAssertions;

    #[Test]
    public function itShouldMapCorrectlyToArray(): void
    {
        $element = new MultiStaticSelectElement(
            $actionId = 'actionId',
            $placeholder = 'placeholder',
            $options = ['options'],
            $initialOptions = ['options'],
            $focusOnLoad = true,
        )->toArray();

        $this->assertMultiStaticSelectElementCorrectlyFormatted(
            $element,
            $actionId,
            $placeholder,
            $focusOnLoad,
            $options,
            $initialOptions,
        );
    }
}

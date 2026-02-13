<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\BlockElement\Component;

use App\Slack\BlockElement\Component\StaticSelectElement;
use App\Tests\Unit\Slack\WithBlockElementAssertions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(StaticSelectElement::class)]
class StaticSelectElementTest extends KernelTestCase
{
    use WithBlockElementAssertions;

    #[Test]
    public function itShouldMapCorrectlyToArray(): void
    {
        $element = new StaticSelectElement(
            $actionId = 'actionId',
            $placeholder = 'placeholder',
            $options = ['options'],
            $focusOnLoad = true,
            $initialOption = ['initialOption'],
        )->toArray();

        $this->assertStaticSelectElementCorrectlyFormatted(
            $element,
            $actionId,
            $placeholder,
            $focusOnLoad,
            $options,
            $initialOption,
        );
    }
}

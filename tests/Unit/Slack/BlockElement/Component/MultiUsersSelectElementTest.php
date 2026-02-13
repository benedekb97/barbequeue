<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\BlockElement\Component;

use App\Slack\BlockElement\Component\MultiUsersSelectElement;
use App\Tests\Unit\Slack\WithBlockElementAssertions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(MultiUsersSelectElement::class)]
class MultiUsersSelectElementTest extends KernelTestCase
{
    use WithBlockElementAssertions;

    #[Test]
    public function itShouldMapCorrectlyToArray(): void
    {
        $element = new MultiUsersSelectElement(
            $actionId = 'actionId',
            $placeholder = 'placeholder',
            $focusOnLoad = true,
        )->toArray();

        $this->assertMultiUsersSelectElementCorrectlyFormatted(
            $element,
            $actionId,
            $placeholder,
            $focusOnLoad,
        );
    }
}

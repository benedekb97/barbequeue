<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\BlockElement\Component;

use App\Slack\BlockElement\BlockElement;
use App\Slack\BlockElement\Component\UrlInputElement;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(UrlInputElement::class)]
class UrlInputElementTest extends KernelTestCase
{
    #[Test]
    public function itShouldReturnCorrectType(): void
    {
        $element = new UrlInputElement();

        $this->assertEquals(BlockElement::URL_INPUT, $element->getType());
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Common;

use App\Slack\Common\Style;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(Style::class)]
class StyleTest extends KernelTestCase
{
    #[Test]
    public function itShouldHaveTwoCases(): void
    {
        $this->assertCount(2, Style::cases());
    }
}

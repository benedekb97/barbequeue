<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Interaction;

use App\Slack\Interaction\InteractionArgumentLocation;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(InteractionArgumentLocation::class)]
class InteractionArgumentLocationTest extends KernelTestCase
{
    #[Test]
    public function itShouldHaveTwoCases(): void
    {
        $this->assertCount(2, InteractionArgumentLocation::cases());
    }
}

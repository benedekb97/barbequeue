<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Interaction;

use App\Slack\Interaction\InteractionType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(InteractionType::class)]
class InteractionTypeTest extends KernelTestCase
{
    #[Test]
    public function itShouldHaveSixCases(): void
    {
        $this->assertCount(5, InteractionType::cases());
    }
}

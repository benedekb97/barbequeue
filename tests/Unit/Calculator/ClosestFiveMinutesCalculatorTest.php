<?php

declare(strict_types=1);

namespace App\Tests\Unit\Calculator;

use App\Calculator\ClosestFiveMinutesCalculator;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(ClosestFiveMinutesCalculator::class)]
class ClosestFiveMinutesCalculatorTest extends KernelTestCase
{
    #[Test]
    public function itShouldRoundUpToClosestFiveMinutesExactly(): void
    {
        $time = CarbonImmutable::parse('2025-01-22 04:01:00');

        $expectedTime = CarbonImmutable::parse('2025-01-22 04:05:00');

        $calculator = new ClosestFiveMinutesCalculator();

        $result = $calculator->calculate($time);

        $this->assertTrue($expectedTime->equalTo($result));
    }

    #[Test]
    public function itShouldRoundDownToClosestFiveMinutesIfMinutesDivisibleByFive(): void
    {
        $time = CarbonImmutable::parse('2025-01-22 04:05:59');

        $expectedTime = CarbonImmutable::parse('2025-01-22 04:05:00');

        $calculator = new ClosestFiveMinutesCalculator();

        $result = $calculator->calculate($time);

        $this->assertTrue($expectedTime->equalTo($result));
    }
}

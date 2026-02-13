<?php

declare(strict_types=1);

namespace App\Calculator;

use Carbon\CarbonImmutable;

class ClosestFiveMinutesCalculator
{
    public function calculate(CarbonImmutable $time): CarbonImmutable
    {
        return $time->setSeconds(0)
            ->setMicroseconds(0)
            ->setMinutes(
                0 === $time->minute % 5
                    ? $time->minute
                    : $time->minute + (5 - $time->minute % 5)
            );
    }
}

<?php

declare(strict_types=1);

namespace App\Slack\Command\Validator\Argument;

use App\Slack\Command\CommandArgument;
use App\Slack\Command\Exception\InvalidArgumentException;

class TimeArgumentValidator implements ArgumentValidatorInterface
{
    private const string REGULAR_EXPRESSION = '/(([0-9]+)h\s?([0-9]+)m?|([0-9]+)h|([0-9]+)m)/';

    public function supports(CommandArgument $argument): bool
    {
        return CommandArgument::TIME === $argument;
    }

    public function validate(CommandArgument $argument, ?string $value): string|int
    {
        if (null === $value) {
            throw new InvalidArgumentException();
        }

        if (is_numeric($value)) {
            $requiredMinutes = (int) $value;

            return $requiredMinutes ?: throw new InvalidArgumentException();
        }

        $matches = [];

        $hasMatched = preg_match(self::REGULAR_EXPRESSION, $value, $matches, PREG_UNMATCHED_AS_NULL);

        if (!$hasMatched) {
            throw new InvalidArgumentException();
        }

        $hours = (int) ($matches[2] ?? $matches[4]);
        $minutes = (int) ($matches[3] ?? $matches[5]);

        $minutes += $hours * 60;

        if (0 === $minutes) {
            throw new InvalidArgumentException();
        }

        return $minutes;
    }
}

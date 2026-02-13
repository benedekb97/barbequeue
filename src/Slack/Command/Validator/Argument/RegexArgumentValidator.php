<?php

declare(strict_types=1);

namespace App\Slack\Command\Validator\Argument;

use App\Slack\Command\CommandArgument;
use App\Slack\Command\Exception\InvalidArgumentException;

readonly class RegexArgumentValidator implements ArgumentValidatorInterface
{
    public function supports(CommandArgument $argument): bool
    {
        return null !== $argument->getRegularExpression();
    }

    /** @throws InvalidArgumentException */
    public function validate(CommandArgument $argument, ?string $value): string|int
    {
        if (null === $value) {
            throw new InvalidArgumentException();
        }

        if (null === $argument->getRegularExpression()) {
            throw new InvalidArgumentException();
        }

        $matches = [];

        preg_match($argument->getRegularExpression(), $value, $matches);

        if (empty($matches)) {
            throw new InvalidArgumentException();
        }

        return $matches[1];
    }
}

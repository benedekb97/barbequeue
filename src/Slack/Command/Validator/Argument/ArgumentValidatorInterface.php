<?php

declare(strict_types=1);

namespace App\Slack\Command\Validator\Argument;

use App\Slack\Command\CommandArgument;
use App\Slack\Command\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag(self::TAG)]
interface ArgumentValidatorInterface
{
    public const string TAG = 'app.command.argument_validator';

    public function supports(CommandArgument $argument): bool;

    /** @throws InvalidArgumentException */
    public function validate(CommandArgument $argument, ?string $value): string|int;
}

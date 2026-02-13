<?php

declare(strict_types=1);

namespace App\Slack\Command;

enum CommandArgument: string
{
    case QUEUE = 'queue';
    case REPOSITORY = 'repository';
    case USER = 'user';
    case TIME = 'time';
    case COMMAND = 'command';

    public function getRegularExpression(): ?string
    {
        return match ($this) {
            self::USER => '/(U[A-Z0-9]{10})/',
            default => null,
        };
    }
}

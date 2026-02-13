<?php

declare(strict_types=1);

namespace App\Enum;

enum QueueBehaviour: string
{
    case ENFORCE_QUEUE = 'enforce-queue';
    case ALLOW_JUMPS = 'allow-jumps';
    case ALLOW_SIMULTANEOUS = 'allow-simultaneous';

    public function getName(): string
    {
        return match ($this) {
            self::ENFORCE_QUEUE => 'Always enforce queue',
            self::ALLOW_JUMPS => 'Allow jumps',
            self::ALLOW_SIMULTANEOUS => 'Allow simultaneous',
        };
    }
}

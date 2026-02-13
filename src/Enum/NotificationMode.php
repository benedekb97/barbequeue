<?php

declare(strict_types=1);

namespace App\Enum;

enum NotificationMode: string
{
    case ALWAYS_NOTIFY = 'always-notify';
    case ONLY_WHEN_ACTIVE = 'only-when-active';

    public function getName(): string
    {
        return match ($this) {
            self::ALWAYS_NOTIFY => 'Always send notifications',
            self::ONLY_WHEN_ACTIVE => 'Only send notifications when I\'m active',
        };
    }
}

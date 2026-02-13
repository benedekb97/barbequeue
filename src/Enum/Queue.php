<?php

declare(strict_types=1);

namespace App\Enum;

enum Queue: string
{
    case SIMPLE = 'simple';
    case DEPLOYMENT = 'deployment';

    public function getName(): string
    {
        return match ($this) {
            self::SIMPLE => 'Simple',
            self::DEPLOYMENT => 'Deployment',
        };
    }
}

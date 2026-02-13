<?php

declare(strict_types=1);

namespace App\DataFixtures\Queue;

enum Repositories: string
{
    case REPOSITORY_A = 'repository-a';
    case REPOSITORY_B = 'repository-b';

    public function getUrl(): ?string
    {
        return match ($this) {
            self::REPOSITORY_A => 'url',
            self::REPOSITORY_B => null,
        };
    }
}

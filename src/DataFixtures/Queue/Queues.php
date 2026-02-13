<?php

declare(strict_types=1);

namespace App\DataFixtures\Queue;

enum Queues: string
{
    case NO_EXPIRY_NO_USER_LIMIT = 'no-expiry-no-user-limit';
    case FIFTEEN_MINUTE_EXPIRY_NO_USER_LIMIT = 'fifteen-minute-expire-no-user-limit';
    case NO_EXPIRY_USER_LIMIT_ONE = 'no-expiry-user-limit-one';
    case NO_EXPIRY_USER_LIMIT_THREE = 'no-expiry-user-limit-three';
    case FIFTEEN_MINUTE_EXPIRY_USER_LIMIT_THREE = 'fifteen-minute-expire-user-limit-three';

    public function getTeamId(): Workspaces
    {
        return match ($this) {
            self::NO_EXPIRY_NO_USER_LIMIT,
            self::FIFTEEN_MINUTE_EXPIRY_NO_USER_LIMIT,
            self::FIFTEEN_MINUTE_EXPIRY_USER_LIMIT_THREE => Workspaces::FIRST,
            default => Workspaces::SECOND,
        };
    }

    public function getMaximumEntriesPerUser(): ?int
    {
        return match ($this) {
            self::NO_EXPIRY_USER_LIMIT_THREE, self::FIFTEEN_MINUTE_EXPIRY_USER_LIMIT_THREE => 3,
            self::NO_EXPIRY_USER_LIMIT_ONE => 1,
            default => null,
        };
    }

    public function getExpiryMinutes(): ?int
    {
        return match ($this) {
            self::FIFTEEN_MINUTE_EXPIRY_USER_LIMIT_THREE,
            self::FIFTEEN_MINUTE_EXPIRY_NO_USER_LIMIT => 15,
            default => null,
        };
    }

    public function getInitialUserCount(): int
    {
        return match ($this) {
            self::FIFTEEN_MINUTE_EXPIRY_NO_USER_LIMIT => 4,
            default => 3,
        };
    }
}

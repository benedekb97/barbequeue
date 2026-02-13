<?php

declare(strict_types=1);

namespace App\DataFixtures\Queue;

enum Workspaces: string
{
    case FIRST = 'first';
    case SECOND = 'second';

    public function getName(): string
    {
        return $this->value;
    }

    /** @return Queues[] */
    public function getQueues(): array
    {
        return match ($this) {
            self::FIRST => [
                Queues::NO_EXPIRY_NO_USER_LIMIT,
                Queues::FIFTEEN_MINUTE_EXPIRY_NO_USER_LIMIT,
                Queues::FIFTEEN_MINUTE_EXPIRY_USER_LIMIT_THREE,
            ],
            self::SECOND => [
                Queues::NO_EXPIRY_USER_LIMIT_THREE,
                Queues::NO_EXPIRY_USER_LIMIT_ONE,
            ],
        };
    }

    /** @return Administrators[] */
    public function getAdministrators(): array
    {
        return match ($this) {
            self::FIRST => Administrators::cases(),
            default => [],
        };
    }

    /** @return Repositories[] */
    public function getRepositories(): array
    {
        return match ($this) {
            self::FIRST => [Repositories::REPOSITORY_A, Repositories::REPOSITORY_B],
            default => [],
        };
    }

    public function getBotToken(): string
    {
        return 'bot-token';
    }
}

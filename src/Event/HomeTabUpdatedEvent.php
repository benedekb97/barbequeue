<?php

declare(strict_types=1);

namespace App\Event;

readonly class HomeTabUpdatedEvent
{
    public function __construct(
        private string $userId,
        private string $teamId,
    ) {
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function getTeamId(): string
    {
        return $this->teamId;
    }
}

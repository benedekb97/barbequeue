<?php

declare(strict_types=1);

namespace App\Event\Repository;

use App\Entity\Repository;

readonly class RepositoryUpdatedEvent
{
    public function __construct(
        private ?Repository $repository,
        private bool $notificationsEnabled = false,
    ) {
    }

    public function getRepository(): ?Repository
    {
        return $this->repository;
    }

    public function areNotificationsEnabled(): bool
    {
        return $this->notificationsEnabled;
    }
}

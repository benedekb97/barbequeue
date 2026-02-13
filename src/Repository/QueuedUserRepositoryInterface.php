<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\QueuedUser;
use App\Entity\Workspace;
use Carbon\CarbonImmutable;
use Doctrine\Persistence\ObjectRepository;

/** @extends ObjectRepository<QueuedUser> */
interface QueuedUserRepositoryInterface extends ObjectRepository
{
    /** @return QueuedUser[] */
    public function findAllExpired(): array;

    public function findOneByIdQueueNameAndWorkspace(int $id, string $queueName, ?Workspace $workspace): ?QueuedUser;

    public function countForWorkspace(
        ?Workspace $workspace,
        ?CarbonImmutable $from = null,
        ?CarbonImmutable $to = null,
        bool $active = false,
        bool $uniqueUsers = false,
    ): int;
}

<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Queue;
use App\Entity\Workspace;
use Doctrine\Persistence\ObjectRepository;

/** @extends ObjectRepository<Queue> */
interface QueueRepositoryInterface extends ObjectRepository
{
    public function findOneByNameAndTeamId(string $name, string $teamId): ?Queue;

    public function findOneByNameAndWorkspace(string $name, ?Workspace $workspace): ?Queue;

    public function findByTeamId(string $teamId): array;
}

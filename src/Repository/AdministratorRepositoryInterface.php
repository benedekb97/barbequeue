<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Administrator;
use App\Entity\Workspace;
use Doctrine\Persistence\ObjectRepository;

/** @extends ObjectRepository<Administrator> */
interface AdministratorRepositoryInterface extends ObjectRepository
{
    public function findOneByUserIdAndWorkspace(string $userId, Workspace $workspace): ?Administrator;

    public function findOneByUserIdAndTeamId(string $userId, string $teamId): ?Administrator;
}

<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Repository;
use App\Entity\Workspace;
use Doctrine\Persistence\ObjectRepository;

/**
 * hehe.
 *
 * @extends ObjectRepository<Repository>
 */
interface RepositoryRepositoryInterface extends ObjectRepository
{
    public function findOneByNameAndWorkspace(string $name, ?Workspace $workspace): ?Repository;

    public function findOneByNameAndTeamId(string $name, string $teamId): ?Repository;

    /** @return Repository[] */
    public function findByTeamId(string $teamId): array;

    public function findOneByIdAndWorkspace(int $id, Workspace $workspace): ?Repository;

    /**
     * @param int[] $ids
     *
     * @return Repository[]
     */
    public function findByIdsAndWorkspace(?array $ids, Workspace $workspace): array;
}

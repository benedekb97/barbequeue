<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Repository;
use App\Entity\Workspace;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * hehe.
 *
 * @extends ServiceEntityRepository<Repository>
 */
class RepositoryRepository extends ServiceEntityRepository implements RepositoryRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Repository::class);
    }

    public function findOneByNameAndWorkspace(string $name, ?Workspace $workspace): ?Repository
    {
        /** @var Repository|null $result */
        $result = $this->createQueryBuilder('r')
            ->where('r.name = :name')
            ->andWhere('r.workspace = :workspace')
            ->setParameter('name', $name)
            ->setParameter('workspace', $workspace)
            ->getQuery()
            ->getOneOrNullResult();

        return $result;
    }

    public function findOneByNameAndTeamId(string $name, string $teamId): ?Repository
    {
        /** @var Repository|null $result */
        $result = $this->createQueryBuilder('r')
            ->join('r.workspace', 'w')
            ->where('r.name = :name')
            ->andWhere('w.slackId = :teamId')
            ->setParameter('name', $name)
            ->setParameter('teamId', $teamId)
            ->getQuery()
            ->getOneOrNullResult();

        return $result;
    }

    public function findByTeamId(string $teamId): array
    {
        /** @var Repository[] $result */
        $result = $this->createQueryBuilder('r')
            ->join('r.workspace', 'w')
            ->where('w.slackId = :teamId')
            ->setParameter('teamId', $teamId)
            ->getQuery()
            ->getResult();

        return $result;
    }

    public function findOneByIdAndWorkspace(int $id, Workspace $workspace): ?Repository
    {
        /** @var Repository|null $result */
        $result = $this->createQueryBuilder('r')
            ->where('r.id = :id')
            ->andWhere('r.workspace = :workspace')
            ->setParameter('id', $id)
            ->setParameter('workspace', $workspace)
            ->getQuery()
            ->getOneOrNullResult();

        return $result;
    }

    public function findByIdsAndWorkspace(?array $ids, Workspace $workspace): array
    {
        if (null === $ids) {
            return [];
        }

        $queryBuilder = $this->createQueryBuilder('r');

        /** @var Repository[] $result */
        $result = $queryBuilder
            ->where($queryBuilder->expr()->in('r.id', ':ids'))
            ->andWhere('r.workspace = :workspace')
            ->setParameter('ids', $ids)
            ->setParameter('workspace', $workspace)
            ->getQuery()
            ->getResult();

        return $result;
    }
}

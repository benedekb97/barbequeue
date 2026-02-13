<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Queue;
use App\Entity\Workspace;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<Queue> */
class QueueRepository extends ServiceEntityRepository implements QueueRepositoryInterface
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, Queue::class);
    }

    public function findOneByNameAndTeamId(string $name, string $teamId): ?Queue
    {
        /** @var Queue|null $queue */
        $queue = $this->createQueryBuilder('q')
            ->join('q.workspace', 'w')
            ->andWhere('q.name = :name')
            ->andWhere('w.slackId = :teamId')
            ->setParameter('name', $name)
            ->setParameter('teamId', $teamId)
            ->getQuery()
            ->getOneOrNullResult();

        return $queue;
    }

    public function findOneByNameAndWorkspace(string $name, ?Workspace $workspace): ?Queue
    {
        if (null === $workspace) {
            return null;
        }

        /** @var Queue|null $queue */
        $queue = $this->createQueryBuilder('q')
            ->where('q.workspace = :workspace')
            ->andWhere('q.name = :name')
            ->setParameter('workspace', $workspace)
            ->setParameter('name', $name)
            ->getQuery()
            ->getOneOrNullResult();

        return $queue;
    }

    public function findByTeamId(string $teamId): array
    {
        /** @var Queue[] $result */
        $result = $this->createQueryBuilder('q')
            ->join('q.workspace', 'w')
            ->where('w.slackId = :teamId')
            ->setParameter('teamId', $teamId)
            ->getQuery()
            ->getResult();

        return $result;
    }
}

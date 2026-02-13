<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\QueuedUser;
use App\Entity\Workspace;
use Carbon\CarbonImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<QueuedUser> */
class QueuedUserRepository extends ServiceEntityRepository implements QueuedUserRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, QueuedUser::class);
    }

    public function findAllExpired(): array
    {
        /** @var QueuedUser[] $result */
        $result = $this->createQueryBuilder('qu')
            ->where('qu.expiresAt <= :now')
            ->setParameter('now', new CarbonImmutable())
            ->getQuery()
            ->getResult();

        return $result;
    }

    public function findOneByIdQueueNameAndWorkspace(int $id, string $queueName, ?Workspace $workspace): ?QueuedUser
    {
        /** @var QueuedUser|null $result */
        $result = $this->createQueryBuilder('qu')
            ->join('qu.queue', 'q')
            ->where('qu.id = :id')
            ->andWhere('q.workspace = :workspace')
            ->andWhere('q.name = :name')
            ->setParameter('id', $id)
            ->setParameter('workspace', $workspace)
            ->setParameter('name', $queueName)
            ->getQuery()
            ->getOneOrNullResult();

        return $result;
    }

    public function countForWorkspace(
        ?Workspace $workspace,
        ?CarbonImmutable $from = null,
        ?CarbonImmutable $to = null,
        bool $active = false,
        bool $uniqueUsers = false,
    ): int {
        $filters = $this->getEntityManager()->getFilters();

        if ($filters->isEnabled('softdeleteable')) {
            $filters->suspend('softdeleteable');
        }

        $queryBuilder = $this->createQueryBuilder('qu');

        $queryBuilder
            ->join('qu.user', 'u')
            ->where('u.workspace = :workspace')
            ->setParameter('workspace', $workspace);

        if (null !== $from) {
            $queryBuilder->andWhere('qu.createdAt >= :from')
                ->setParameter('from', $from);
        }

        if (null !== $to) {
            $queryBuilder->andWhere('qu.createdAt <= :to')
                ->setParameter('to', $to);
        }

        if ($active) {
            $queryBuilder->andWhere('qu.deletedAt IS NULL');
        }

        if ($uniqueUsers) {
            $queryBuilder->select($queryBuilder->expr()->countDistinct('u.id'));
        } else {
            $queryBuilder->select('count(qu.id)');
        }

        $result = (int) $queryBuilder
            ->getQuery()
            ->getSingleScalarResult();

        if ($filters->isSuspended('softdeleteable')) {
            $filters->enable('softdeleteable');
        }

        return $result;
    }
}

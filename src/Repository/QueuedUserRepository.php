<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Queue;
use App\Entity\QueuedUser;
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

    public function findAllQueuesWithExpiredUsers(): array
    {
        /** @var Queue[] $result */
        $result = $this->createQueryBuilder('qu')
            ->select('qu.queue')
            ->distinct()
            ->where('qu.expiredAt <= :now')
            ->setParameter('now', new CarbonImmutable())
            ->getQuery()
            ->getResult();

        return $result;
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
}

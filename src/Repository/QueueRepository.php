<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Queue;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<Queue> */
class QueueRepository extends ServiceEntityRepository implements QueueRepositoryInterface
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, Queue::class);
    }

    public function findOneByNameAndDomain(string $name, string $domain): ?Queue
    {
        return $this->createQueryBuilder('q')
            ->andWhere('q.name = :name')
            ->andWhere('q.domain = :domain')
            ->setParameter('name', $name)
            ->setParameter('domain', $domain)
            ->getQuery()
            ->getOneOrNullResult();
    }
}

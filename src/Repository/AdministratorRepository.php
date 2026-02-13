<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Administrator;
use App\Entity\Workspace;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<Administrator> */
class AdministratorRepository extends ServiceEntityRepository implements AdministratorRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Administrator::class);
    }

    public function findOneByUserIdAndWorkspace(string $userId, Workspace $workspace): ?Administrator
    {
        /** @var Administrator|null $result */
        $result = $this->createQueryBuilder('a')
            ->join('a.user', 'u')
            ->where('u.slackId = :userId')
            ->andWhere('a.workspace = :workspace')
            ->setParameter('userId', $userId)
            ->setParameter('workspace', $workspace)
            ->getQuery()
            ->getOneOrNullResult();

        return $result;
    }

    public function findOneByUserIdAndTeamId(string $userId, string $teamId): ?Administrator
    {
        /** @var Administrator|null $result */
        $result = $this->createQueryBuilder('a')
            ->join('a.workspace', 'w')
            ->join('a.user', 'u')
            ->where('u.slackId = :userId')
            ->andWhere('w.slackId = :teamId')
            ->setParameter('userId', $userId)
            ->setParameter('teamId', $teamId)
            ->getQuery()
            ->getOneOrNullResult();

        return $result;
    }
}

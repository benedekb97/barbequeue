<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<User> */
class UserRepository extends ServiceEntityRepository implements UserRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function findOneBySlackIdAndWorkspaceSlackId(string $slackId, string $workspaceSlackId): ?User
    {
        /** @var User $user */
        $user = $this->createQueryBuilder('u')
            ->join('u.workspace', 'w')
            ->where('u.slackId = :slackId')
            ->andWhere('w.slackId = :workspaceSlackId')
            ->setParameter('slackId', $slackId)
            ->setParameter('workspaceSlackId', $workspaceSlackId)
            ->getQuery()
            ->getOneOrNullResult();

        return $user;
    }
}

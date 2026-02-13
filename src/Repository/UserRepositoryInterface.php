<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\User;
use Doctrine\Persistence\ObjectRepository;

/** @extends ObjectRepository<User> */
interface UserRepositoryInterface extends ObjectRepository
{
    public function findOneBySlackIdAndWorkspaceSlackId(string $slackId, string $workspaceSlackId): ?User;
}

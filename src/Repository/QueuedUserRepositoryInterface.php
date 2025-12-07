<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Queue;
use App\Entity\QueuedUser;
use Doctrine\Persistence\ObjectRepository;

/** @extends ObjectRepository<QueuedUser> */
interface QueuedUserRepositoryInterface extends ObjectRepository
{
    /** @return Queue[] */
    public function findAllQueuesWithExpiredUsers(): array;

    /** @return QueuedUser[] */
    public function findAllExpired(): array;
}

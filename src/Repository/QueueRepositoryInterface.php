<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Queue;
use Doctrine\Persistence\ObjectRepository;

/** @extends ObjectRepository<Queue> */
interface QueueRepositoryInterface extends ObjectRepository
{
    public function findOneByNameAndDomain(string $name, string $domain): ?Queue;
}

<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Queue;
use App\Entity\Workspace;

readonly class QueueFactory
{
    public function create(
        string $name,
        ?Workspace $workspace,
        ?int $maximumEntriesPerUser,
        ?int $expiryMinutes,
    ): Queue {
        return new Queue()
            ->setName($name)
            ->setWorkspace($workspace)
            ->setMaximumEntriesPerUser($maximumEntriesPerUser)
            ->setExpiryMinutes($expiryMinutes);
    }
}

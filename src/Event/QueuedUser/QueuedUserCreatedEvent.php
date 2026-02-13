<?php

declare(strict_types=1);

namespace App\Event\QueuedUser;

use App\Entity\QueuedUser;

readonly class QueuedUserCreatedEvent
{
    public function __construct(
        private QueuedUser $queuedUser,
    ) {
    }

    public function getQueuedUser(): QueuedUser
    {
        return $this->queuedUser;
    }
}

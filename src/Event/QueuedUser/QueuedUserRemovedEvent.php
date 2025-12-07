<?php

declare(strict_types=1);

namespace App\Event\QueuedUser;

use App\Entity\Queue;
use App\Entity\QueuedUser;

readonly class QueuedUserRemovedEvent
{
    public function __construct(
        private QueuedUser $queuedUser,
        private Queue $queue,
        private bool $forced = false,
    ) {}

    public function getQueuedUser(): QueuedUser
    {
        return $this->queuedUser;
    }

    public function getQueue(): Queue
    {
        return $this->queue;
    }

    public function isForced(): bool
    {
        return $this->forced;
    }
}

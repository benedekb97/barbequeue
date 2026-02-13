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
        private bool $notificationRequired = false,
        private bool $automatic = false,
    ) {
    }

    public function getQueuedUser(): QueuedUser
    {
        return $this->queuedUser;
    }

    public function getQueue(): Queue
    {
        return $this->queue;
    }

    public function isNotificationRequired(): bool
    {
        return $this->notificationRequired;
    }

    public function isAutomatic(): bool
    {
        return $this->automatic;
    }
}

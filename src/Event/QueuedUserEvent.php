<?php

declare(strict_types=1);

namespace App\Event;

use App\Entity\QueuedUser;

readonly class QueuedUserEvent
{
    public const string CREATED = 'created';
    public const string REMOVED = 'removed';

    public function __construct(
        private QueuedUser $queuedUser,
    ) {}

    public function getQueuedUser(): QueuedUser
    {
        return $this->queuedUser;
    }
}

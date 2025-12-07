<?php

declare(strict_types=1);

namespace App\Event\Queue;

use App\Entity\Queue;

readonly class QueueUpdatedEvent
{
    public function __construct(
        private Queue $queue,
    ) {}

    public function getQueue(): Queue
    {
        return $this->queue;
    }
}

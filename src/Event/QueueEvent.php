<?php

declare(strict_types=1);

namespace App\Event;

use App\Entity\Queue;

readonly class QueueEvent
{
    public const string UPDATED = 'updated';

    public function __construct(
        private Queue $queue,
    ) {}

    public function getQueue(): Queue
    {
        return $this->queue;
    }
}

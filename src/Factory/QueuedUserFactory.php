<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Queue;
use App\Entity\QueuedUser;

readonly class QueuedUserFactory
{
    public function createForQueue(Queue $queue): QueuedUser
    {
        $queuedUser = new QueuedUser()->setCreatedAtNow();

        $queue->addQueuedUser($queuedUser);

        return $queuedUser;
    }
}

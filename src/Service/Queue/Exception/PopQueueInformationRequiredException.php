<?php

declare(strict_types=1);

namespace App\Service\Queue\Exception;

use App\Entity\Queue;

class PopQueueInformationRequiredException extends \Exception
{
    public function __construct(
        private readonly Queue $queue,
    ) {
        parent::__construct();
    }

    public function getQueue(): Queue
    {
        return $this->queue;
    }
}

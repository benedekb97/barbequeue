<?php

declare(strict_types=1);

namespace App\Service\Queue\Exception;

use App\Entity\Queue;

class InvalidDeploymentUrlException extends \Exception
{
    public function __construct(
        private readonly string $deploymentLink,
        private readonly Queue $queue,
    ) {
        parent::__construct();
    }

    public function getDeploymentLink(): string
    {
        return $this->deploymentLink;
    }

    public function getQueue(): Queue
    {
        return $this->queue;
    }
}

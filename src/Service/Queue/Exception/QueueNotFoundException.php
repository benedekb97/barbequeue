<?php

declare(strict_types=1);

namespace App\Service\Queue\Exception;

class QueueNotFoundException extends \Exception
{
    public function __construct(
        private readonly string $queueName,
        private readonly string $teamId,
        private readonly string $userId,
    ) {
        parent::__construct();
    }

    public function getQueueName(): string
    {
        return $this->queueName;
    }

    public function getTeamId(): string
    {
        return $this->teamId;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }
}

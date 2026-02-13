<?php

declare(strict_types=1);

namespace App\Service\Queue\Leave;

use App\Entity\QueuedUser;
use App\Entity\Repository;
use App\Service\Queue\Context\ContextType;
use App\Service\Queue\Context\QueueContext;
use App\Service\Queue\Context\QueueContextInterface;

class LeaveQueueContext extends QueueContext implements QueueContextInterface
{
    private QueuedUser $queuedUser;

    private ?Repository $repository = null;

    public function __construct(
        string $queueName,
        string $teamId,
        string $userId,
        private readonly ?int $queuedUserId = null,
    ) {
        parent::__construct($queueName, $teamId, $userId);
    }

    public function getType(): ContextType
    {
        return ContextType::LEAVE;
    }

    public function getQueuedUserId(): ?int
    {
        return $this->queuedUserId;
    }

    public function getQueuedUser(): QueuedUser
    {
        return $this->queuedUser;
    }

    public function setQueuedUser(QueuedUser $queuedUser): void
    {
        $this->queuedUser = $queuedUser;
    }

    public function getRepository(): ?Repository
    {
        return $this->repository;
    }

    public function setRepository(?Repository $repository): void
    {
        $this->repository = $repository;
    }
}

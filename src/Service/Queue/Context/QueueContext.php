<?php

declare(strict_types=1);

namespace App\Service\Queue\Context;

use App\Entity\Queue;
use App\Entity\User;
use App\Entity\Workspace;
use Symfony\Component\Uid\Uuid;

abstract class QueueContext implements QueueContextInterface
{
    private Queue $queue;

    private User $user;

    private Workspace $workspace;

    private string $id;

    public function __construct(
        private readonly string|int $queueIdentifier,
        private readonly string $teamId,
        private readonly string $userId,
    ) {
        $this->id = Uuid::v4()->toString();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getQueue(): Queue
    {
        return $this->queue;
    }

    public function hasQueue(): bool
    {
        return isset($this->queue);
    }

    public function setQueue(Queue $queue): void
    {
        $this->queue = $queue;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function hasUser(): bool
    {
        return isset($this->user);
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    public function getWorkspace(): Workspace
    {
        return $this->workspace;
    }

    public function hasWorkspace(): bool
    {
        return isset($this->workspace);
    }

    public function setWorkspace(Workspace $workspace): void
    {
        $this->workspace = $workspace;
    }

    public function getTeamId(): string
    {
        return $this->teamId;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function getQueueIdentifier(): string|int
    {
        return $this->queueIdentifier;
    }
}

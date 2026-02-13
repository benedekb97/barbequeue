<?php

declare(strict_types=1);

namespace App\Form\QueuedUser\Data;

use App\Entity\Queue;
use App\Entity\User;

class QueuedUserData
{
    private User $user;

    private string $queueName;

    private ?int $expiryMinutes = null;

    private Queue $queue;

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getQueueName(): string
    {
        return $this->queueName;
    }

    public function setQueueName(string $queueName): static
    {
        $this->queueName = $queueName;

        return $this;
    }

    public function getExpiryMinutes(): ?int
    {
        return $this->expiryMinutes;
    }

    public function setExpiryMinutes(int $expiryMinutes): static
    {
        $this->expiryMinutes = $expiryMinutes;

        return $this;
    }

    public function getQueue(): Queue
    {
        return $this->queue;
    }

    public function setQueue(Queue $queue): static
    {
        $this->queue = $queue;

        return $this;
    }
}

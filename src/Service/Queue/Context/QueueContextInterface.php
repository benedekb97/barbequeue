<?php

declare(strict_types=1);

namespace App\Service\Queue\Context;

use App\Entity\Queue;
use App\Entity\User;
use App\Entity\Workspace;

interface QueueContextInterface
{
    public function getId(): string;

    public function getType(): ContextType;

    public function getQueue(): Queue;

    public function hasQueue(): bool;

    public function setQueue(Queue $queue): void;

    public function getUser(): User;

    public function hasUser(): bool;

    public function setUser(User $user): void;

    public function getTeamId(): string;

    public function getUserId(): string;

    public function getQueueIdentifier(): string|int;

    public function getWorkspace(): Workspace;

    public function hasWorkspace(): bool;

    public function setWorkspace(Workspace $workspace): void;
}

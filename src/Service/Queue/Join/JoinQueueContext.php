<?php

declare(strict_types=1);

namespace App\Service\Queue\Join;

use App\Entity\QueuedUser;
use App\Entity\Repository;
use App\Entity\User;
use App\Service\Queue\Context\ContextType;
use App\Service\Queue\Context\QueueContext;
use App\Service\Queue\Context\QueueContextInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class JoinQueueContext extends QueueContext implements QueueContextInterface
{
    private QueuedUser $queuedUser;

    private ?Repository $repository = null;

    /** @var Collection<int, User> */
    private Collection $users;

    public function __construct(
        string $queueName,
        string $teamId,
        string $userId,
        private readonly string $userName,
        private readonly ?int $requiredMinutes = null,
        private readonly ?string $deploymentDescription = null,
        private ?string $deploymentLink = null,
        private readonly ?int $deploymentRepositoryId = null,
        /** @var string[] $notifyUsers */
        private readonly array $notifyUsers = [],
    ) {
        parent::__construct($queueName, $teamId, $userId);

        $this->users = new ArrayCollection();
    }

    public function getType(): ContextType
    {
        return ContextType::JOIN;
    }

    public function getRequiredMinutes(): ?int
    {
        return $this->requiredMinutes;
    }

    public function getDeploymentDescription(): ?string
    {
        return $this->deploymentDescription;
    }

    public function getDeploymentLink(): ?string
    {
        return $this->deploymentLink;
    }

    public function setDeploymentLink(?string $deploymentLink): void
    {
        $this->deploymentLink = $deploymentLink;
    }

    public function getDeploymentRepositoryId(): ?int
    {
        return $this->deploymentRepositoryId;
    }

    /** @return string[] */
    public function getNotifyUsers(): array
    {
        return $this->notifyUsers;
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

    /** @return Collection<int, User> */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): void
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
        }
    }

    public function getUserName(): string
    {
        return $this->userName;
    }
}

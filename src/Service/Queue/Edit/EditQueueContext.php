<?php

declare(strict_types=1);

namespace App\Service\Queue\Edit;

use App\Entity\Repository;
use App\Enum\QueueBehaviour;
use App\Service\Queue\Context\ContextType;
use App\Service\Queue\Context\QueueContext;
use App\Service\Queue\Context\QueueContextInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class EditQueueContext extends QueueContext implements QueueContextInterface
{
    private QueueBehaviour $behaviour;

    /** @var Collection<int, Repository> */
    private Collection $repositories;

    public function __construct(
        int $queueId,
        string $teamId,
        string $userId,
        private readonly ?int $maximumEntriesPerUser,
        private ?int $expiryMinutes,
        /** @var int[]|null $repositoryIds */
        private readonly ?array $repositoryIds,
        private readonly ?string $queueBehaviour,
    ) {
        parent::__construct($queueId, $teamId, $userId);

        $this->repositories = new ArrayCollection();
    }

    public function getType(): ContextType
    {
        return ContextType::EDIT;
    }

    public function getMaximumEntriesPerUser(): ?int
    {
        return $this->maximumEntriesPerUser;
    }

    public function getExpiryMinutes(): ?int
    {
        return $this->expiryMinutes;
    }

    public function setExpiryMinutes(int $expiryMinutes): void
    {
        $this->expiryMinutes = $expiryMinutes;
    }

    /** @return int[]|null */
    public function getRepositoryIds(): ?array
    {
        return $this->repositoryIds;
    }

    public function getQueueBehaviour(): ?string
    {
        return $this->queueBehaviour;
    }

    public function getBehaviour(): QueueBehaviour
    {
        return $this->behaviour;
    }

    public function setBehaviour(QueueBehaviour $behaviour): void
    {
        $this->behaviour = $behaviour;
    }

    /** @return Collection<int, Repository> */
    public function getRepositories(): Collection
    {
        return $this->repositories;
    }

    public function addRepository(Repository $repository): void
    {
        $this->repositories->add($repository);
    }
}

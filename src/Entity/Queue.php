<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[Entity]
#[UniqueConstraint(columns: ['name', 'domain'])]
class Queue
{
    use TimestampableEntity;

    #[Column(type: Types::BIGINT)]
    #[GeneratedValue]
    #[Id]
    private ?int $id = null;

    #[Column(type: Types::STRING)]
    private ?string $name = null;

    #[Column(type: Types::STRING)]
    private ?string $domain = null;

    /** @var Collection<int, QueuedUser> $queuedUsers */
    #[OneToMany(QueuedUser::class, mappedBy: 'queue')]
    private Collection $queuedUsers;

    #[Column(type: Types::INTEGER, nullable: true)]
    private ?int $expiryMinutes = null;

    #[Column(type: Types::INTEGER, nullable: true)]
    private ?int $maximumEntriesPerUser = null;

    public function __construct()
    {
        $this->queuedUsers = new ArrayCollection();
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function canJoin(string $userId): bool
    {
        if (!isset($this->maximumEntriesPerUser)) {
            return true;
        }

        return $this->getQueuedUsersByUserId($userId)->count() < $this->maximumEntriesPerUser;
    }

    public function canLeave(string $userId): bool
    {
        return !$this->getQueuedUsersByUserId($userId)->isEmpty();
    }

    public function getMaximumEntriesPerUser(): ?int
    {
        return $this->maximumEntriesPerUser;
    }

    /** @return Collection<int, QueuedUser> */
    public function getQueuedUsers(): Collection
    {
        return $this->queuedUsers;
    }

    public function addQueuedUser(QueuedUser $queuedUser): static
    {
        if (!$this->queuedUsers->contains($queuedUser)) {
            $this->queuedUsers->add($queuedUser);
            $queuedUser->setQueue($this);
        }

        return $this;
    }

    public function removeQueuedUser(QueuedUser $queuedUser): static
    {
        if ($this->queuedUsers->contains($queuedUser)) {
            $this->queuedUsers->removeElement($queuedUser);
            $queuedUser->setQueue(null);
        }

        return $this;
    }

    /** @return Collection<int, QueuedUser> */
    public function getQueuedUsersByUserId(string $userId): Collection
    {
        return $this->queuedUsers->filter(function (QueuedUser $user) use ($userId) {
            return $userId === $user->getUserId();
        });
    }

    public function getFirstPlace(string $userId): ?QueuedUser
    {
        /** @var QueuedUser[] $users */
        $users = $this->getQueuedUsersByUserId($userId)->toArray();

        if (empty($users)) {
            return null;
        }

        uasort($users, function (QueuedUser $first, QueuedUser $second) {
            return $first->getCreatedAt() <=> $second->getCreatedAt();
        });

        return reset($users);
    }

    public function getLastPlace(string $userId): ?QueuedUser
    {
        /** @var QueuedUser[] $users */
        $users = $this->getQueuedUsersByUserId($userId)->toArray();

        if (empty($users)) {
            return null;
        }

        uasort($users, function (QueuedUser $first, QueuedUser $second) {
            return $second->getCreatedAt() <=> $first->getCreatedAt();
        });

        return reset($users);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDomain(): ?string
    {
        return $this->domain;
    }

    public function getExpiryMinutes(): ?int
    {
        return $this->expiryMinutes;
    }
}

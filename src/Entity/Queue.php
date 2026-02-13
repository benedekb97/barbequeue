<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\Queue as QueueType;
use App\Enum\QueueBehaviour;
use Carbon\CarbonImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\DiscriminatorColumn;
use Doctrine\ORM\Mapping\DiscriminatorMap;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\InheritanceType;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\PrePersist;
use Doctrine\ORM\Mapping\PreUpdate;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Positive;

#[Entity]
#[InheritanceType('SINGLE_TABLE')]
#[DiscriminatorColumn(name: 'type', type: Types::STRING)]
#[DiscriminatorMap([
    'deployment' => DeploymentQueue::class,
    'simple' => Queue::class,
])]
#[HasLifecycleCallbacks]
#[UniqueConstraint(columns: ['name', 'workspace_id'])]

#[UniqueEntity(['workspace', 'name'], entityClass: Queue::class, errorPath: 'name')]
class Queue
{
    #[Column(type: Types::BIGINT)]
    #[GeneratedValue]
    #[Id]
    private ?int $id = null;

    #[Column(type: Types::DATETIME_IMMUTABLE)]
    private ?CarbonImmutable $createdAt = null;

    #[Column(type: Types::DATETIME_IMMUTABLE)]
    private ?CarbonImmutable $updatedAt = null;

    #[Column(type: Types::STRING)]
    #[Groups(['queue', 'repository', 'queued-user'])]

    #[NotBlank]
    private ?string $name = null;

    #[ManyToOne(targetEntity: Workspace::class, inversedBy: 'queues')]
    #[JoinColumn(name: 'workspace_id')]
    #[NotNull]
    private ?Workspace $workspace = null;

    /** @var Collection<int, QueuedUser> $queuedUsers */
    #[OneToMany(QueuedUser::class, mappedBy: 'queue')]
    #[Groups(['queue'])]

    #[Count(exactly: 0, exactMessage: 'Queue with queued users cannot be deleted.', groups: ['delete'])]
    private Collection $queuedUsers;

    #[Column(type: Types::INTEGER, nullable: true)]
    #[Groups(['queue'])]
    #[Positive]
    private ?int $expiryMinutes = null;

    #[Column(type: Types::INTEGER, nullable: true)]
    #[Groups(['queue'])]
    #[Positive]
    private ?int $maximumEntriesPerUser = null;

    public function __construct()
    {
        $this->queuedUsers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setMaximumEntriesPerUser(?int $maximumEntriesPerUser): static
    {
        $this->maximumEntriesPerUser = $maximumEntriesPerUser;

        return $this;
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

    public function setExpiryMinutes(?int $expiryMinutes): static
    {
        $this->expiryMinutes = $expiryMinutes;

        return $this;
    }

    public function getExpiryMinutes(): ?int
    {
        return $this->expiryMinutes;
    }

    public function getWorkspace(): ?Workspace
    {
        return $this->workspace;
    }

    public function setWorkspace(?Workspace $workspace): static
    {
        $this->workspace = $workspace;

        return $this;
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

    public function canRelease(string $userId): bool
    {
        return ($this->getFirstPlace()?->getUser()?->getSlackId() ?? null) === $userId;
    }

    /** @return Collection<int, QueuedUser> */
    public function getQueuedUsersByUserId(string $userId): Collection
    {
        return $this->queuedUsers->filter(function (QueuedUser $user) use ($userId) {
            return $userId === $user->getUser()?->getSlackId();
        });
    }

    public function getFirstPlace(): ?QueuedUser
    {
        $queuedUsers = $this->getSortedUsers();

        return reset($queuedUsers) ?: null;
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

    /** @return QueuedUser[] */
    public function getSortedUsers(): array
    {
        if ($this->queuedUsers->isEmpty()) {
            return [];
        }

        /** @var QueuedUser[] $users */
        $users = $this->queuedUsers->toArray();

        uasort($users, function (QueuedUser $first, QueuedUser $second) {
            return $first->getCreatedAt() <=> $second->getCreatedAt();
        });

        return array_values($users);
    }

    public function hasQueuedUserWithExpiryMinutes(): bool
    {
        return $this->queuedUsers->exists(function (int $key, QueuedUser $queuedUser) {
            return null !== $queuedUser->getExpiryMinutes();
        });
    }

    #[PrePersist]
    public function setCreatedAtNow(): void
    {
        $this->createdAt = CarbonImmutable::now();
    }

    public function getCreatedAt(): ?CarbonImmutable
    {
        return $this->createdAt;
    }

    #[PreUpdate]
    #[PrePersist]
    public function setUpdatedAtNow(): void
    {
        $this->updatedAt = CarbonImmutable::now();
    }

    public function getUpdatedAt(): ?CarbonImmutable
    {
        return $this->updatedAt;
    }

    public function getBehaviour(): QueueBehaviour
    {
        return QueueBehaviour::ENFORCE_QUEUE;
    }

    /** @return string[] */
    public function getPlacements(string $userId): array
    {
        $allUsers = array_values($this->getSortedUsers());
        $queuedPlaces = $this->getQueuedUsersByUserId($userId);

        $places = [];

        foreach ($allUsers as $key => $user) {
            if ($queuedPlaces->contains($user)) {
                $places[] = $key + 1;
            }
        }

        return array_map(function (int $number) {
            return $number.$this->getOrdinalSuffix($number);
        }, $places);
    }

    public function getPlacementString(string $userId): string
    {
        $placements = $this->getPlacements($userId);

        if (1 === count($placements)) {
            return reset($placements);
        }

        return implode(', ', array_slice($placements, 0, -1)).' and '.end($placements);
    }

    public function getType(): QueueType
    {
        return match (true) {
            $this instanceof DeploymentQueue => QueueType::DEPLOYMENT,
            default => QueueType::SIMPLE,
        };
    }

    private function getOrdinalSuffix(int $number): string
    {
        return match ($number % 10) {
            1 => 'st',
            2 => 'nd',
            3 => 'rd',
            default => 'th',
        };
    }
}

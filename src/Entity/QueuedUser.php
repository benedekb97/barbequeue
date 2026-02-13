<?php

declare(strict_types=1);

namespace App\Entity;

use Carbon\CarbonImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\DiscriminatorColumn;
use Doctrine\ORM\Mapping\DiscriminatorMap;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\InheritanceType;
use Doctrine\ORM\Mapping\ManyToOne;
use Gedmo\Mapping\Annotation\SoftDeleteable;
use Gedmo\Mapping\Annotation\Timestampable;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Symfony\Component\Serializer\Attribute\Groups;

#[InheritanceType('SINGLE_TABLE')]
#[DiscriminatorColumn(name: 'type', type: Types::STRING)]
#[DiscriminatorMap([
    'simple' => QueuedUser::class,
    'deployment' => Deployment::class,
])]
#[Entity]
#[SoftDeleteable]
class QueuedUser
{
    use SoftDeleteableEntity;

    #[Id]
    #[Column(type: Types::INTEGER)]
    #[GeneratedValue]
    #[Groups(['queue', 'repository', 'queued-user'])]
    private ?int $id = null;

    #[ManyToOne(targetEntity: User::class, cascade: ['persist'])]
    #[Groups(['queue', 'repository', 'queued-user'])]
    private ?User $user = null;

    #[ManyToOne(targetEntity: Queue::class, inversedBy: 'queuedUsers')]
    #[Groups(['repository', 'queued-user'])]
    private ?Queue $queue = null;

    #[Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    #[Groups(['queue', 'repository', 'queued-user'])]
    private ?CarbonImmutable $expiresAt = null;

    #[Column(type: Types::INTEGER, nullable: true)]
    #[Groups(['queue', 'repository', 'queued-user'])]
    private ?int $expiryMinutes = null;

    #[Column(type: Types::DATETIME_IMMUTABLE)]
    #[Timestampable(on: 'create')]
    #[Groups(['queue', 'repository', 'queued-user'])]
    private ?CarbonImmutable $createdAt = null;

    #[Column(type: Types::DATETIME_IMMUTABLE)]
    #[Timestampable(on: 'update')]
    private ?CarbonImmutable $updatedAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQueue(): ?Queue
    {
        return $this->queue;
    }

    public function setQueue(?Queue $queue): static
    {
        $this->queue = $queue;

        return $this;
    }

    public function getExpiresAt(): ?CarbonImmutable
    {
        return $this->expiresAt;
    }

    public function setExpiresAt(?CarbonImmutable $expiresAt): static
    {
        $this->expiresAt = $expiresAt;

        return $this;
    }

    public function getUserLink(): string
    {
        return sprintf('<@%s>', $this->user?->getSlackId());
    }

    public function getExpiryMinutes(): ?int
    {
        return $this->expiryMinutes;
    }

    public function setExpiryMinutes(?int $expiryMinutes): static
    {
        $this->expiryMinutes = $expiryMinutes;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getExpiryMinutesLeft(): ?int
    {
        if (null === $this->expiryMinutes) {
            return null;
        }

        if (null !== $this->expiresAt) {
            return (int) ceil($this->expiresAt->diffInMinutes(absolute: true));
        }

        return $this->expiryMinutes;
    }

    public function getCreatedAt(): ?CarbonImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?CarbonImmutable $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function setCreatedAtNow(): static
    {
        $this->createdAt = CarbonImmutable::now();

        return $this;
    }

    public function getUpdatedAt(): ?CarbonImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?CarbonImmutable $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    public function setUpdatedAtNow(): static
    {
        $this->updatedAt = CarbonImmutable::now();

        return $this;
    }
}

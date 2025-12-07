<?php

declare(strict_types=1);

namespace App\Entity;

use Carbon\CarbonImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\ManyToOne;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[Entity]
class QueuedUser
{
    use TimestampableEntity;

    #[Id]
    #[Column(type: Types::INTEGER)]
    #[GeneratedValue]
    private ?int $id = null;

    #[Column(type: Types::STRING)]
    private string $userId;

    #[ManyToOne(targetEntity: Queue::class, inversedBy: 'queuedUsers')]
    private ?Queue $queue = null;

    #[Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?CarbonImmutable $expiresAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function setUserId(string $userId): static
    {
        $this->userId = $userId;

        return $this;
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
        return sprintf('<@%s>', $this->userId);
    }
}

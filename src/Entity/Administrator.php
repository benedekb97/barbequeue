<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\MaxDepth;

#[Entity]
#[UniqueConstraint(columns: ['user_id', 'workspace_id'])]
class Administrator
{
    use TimestampableEntity;

    #[Id]
    #[GeneratedValue]
    #[Column(type: Types::BIGINT)]
    #[Groups(['me'])]
    private ?int $id = null;

    #[ManyToOne(targetEntity: Administrator::class)]
    #[Groups(['me'])]
    #[MaxDepth(1)]
    private ?Administrator $addedBy = null;

    #[ManyToOne(targetEntity: Workspace::class, inversedBy: 'administrators')]
    #[JoinColumn(nullable: false)]
    private ?Workspace $workspace = null;

    #[OneToOne(targetEntity: User::class, cascade: ['persist'])]
    private ?User $user = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAddedBy(): ?Administrator
    {
        return $this->addedBy;
    }

    public function setAddedBy(?Administrator $addedBy): static
    {
        $this->addedBy = $addedBy;

        return $this;
    }

    public function getUserId(): ?string
    {
        return $this->user?->getSlackId();
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

    public function getUserLink(): string
    {
        return sprintf('<@%s>', $this->getUserId());
    }

    public function isAddedBy(Administrator $administrator): bool
    {
        if ($this->addedBy === $administrator) {
            return true;
        }

        if (null === $this->addedBy) {
            return false;
        }

        return $this->addedBy->isAddedBy($administrator);
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
}

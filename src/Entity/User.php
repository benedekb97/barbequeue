<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Attribute\Groups;

#[Entity]
#[Table(name: '`user`')]
class User implements UserInterface
{
    public const string ROLE_USER = 'ROLE_USER';
    public const string ROLE_ADMINISTRATOR = 'ROLE_ADMINISTRATOR';

    #[Id]
    #[GeneratedValue]
    #[Column(type: Types::INTEGER)]
    #[Groups(['me'])]
    private ?int $id = null;

    #[Column(type: Types::STRING)]
    #[Groups(['queue', 'repository', 'me', 'queued-user'])]
    private ?string $slackId = null;

    #[OneToOne(targetEntity: Administrator::class, mappedBy: 'user')]
    #[Groups(['me'])]
    private ?Administrator $administrator = null;

    #[ManyToOne(targetEntity: Workspace::class, inversedBy: 'users')]
    private ?Workspace $workspace = null;

    #[OneToOne(targetEntity: NotificationSettings::class, mappedBy: 'user', cascade: ['persist'])]
    #[Groups(['me'])]
    private ?NotificationSettings $notificationSettings = null;

    #[Column(type: Types::STRING, nullable: true)]
    #[Groups(['queue', 'repository', 'me', 'queued-user'])]
    private ?string $name = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setSlackId(string $slackId): static
    {
        $this->slackId = $slackId;

        return $this;
    }

    public function getSlackId(): ?string
    {
        return $this->slackId;
    }

    public function getAdministrator(): ?Administrator
    {
        return $this->administrator;
    }

    public function setAdministrator(?Administrator $administrator): static
    {
        $this->administrator = $administrator;

        return $this;
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

    public function getNotificationSettings(): ?NotificationSettings
    {
        return $this->notificationSettings;
    }

    public function setNotificationSettings(?NotificationSettings $notificationSettings): static
    {
        if ($this->notificationSettings !== $notificationSettings) {
            $this->notificationSettings = $notificationSettings;
            $notificationSettings?->setUser($this);
        }

        return $this;
    }

    public function getRoles(): array
    {
        $roles = [self::ROLE_USER];

        if ($this->isAdministrator()) {
            $roles[] = self::ROLE_ADMINISTRATOR;
        }

        return $roles;
    }

    public function eraseCredentials(): void
    {
    }

    public function getUserIdentifier(): string
    {
        return empty($this->slackId) ? 'null' : $this->slackId;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function isAdministrator(): bool
    {
        return null !== $this->administrator;
    }
}

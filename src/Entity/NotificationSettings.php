<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\NotificationMode;
use App\Enum\NotificationSetting;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\OneToOne;
use Symfony\Component\Serializer\Attribute\Groups;

#[Entity]
class NotificationSettings
{
    #[Id]
    #[GeneratedValue]
    #[Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[OneToOne(targetEntity: User::class, inversedBy: 'notificationSettings')]
    private ?User $user = null;

    #[Column(type: Types::ENUM, enumType: NotificationMode::class)]
    #[Groups('me')]
    private NotificationMode $mode = NotificationMode::ALWAYS_NOTIFY;

    /** @var bool[] */
    #[Column(type: Types::JSON)]
    #[Groups(['me'])]
    private array $settings = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        if ($this->user !== $user) {
            $this->user = $user;
            $user?->setNotificationSettings($this);
        }

        return $this;
    }

    public function getMode(): NotificationMode
    {
        return $this->mode;
    }

    public function setMode(NotificationMode $mode): void
    {
        $this->mode = $mode;
    }

    public function getSettings(): array
    {
        return $this->settings;
    }

    /** @param bool[] $settings */
    public function setSettings(array $settings): void
    {
        $this->settings = $settings;
    }

    public function isSettingEnabled(NotificationSetting $setting): bool
    {
        return $this->settings[$setting->value] ?? true;
    }

    public function setSetting(NotificationSetting $setting, bool $enabled): void
    {
        $this->settings[$setting->value] = $enabled;
    }
}

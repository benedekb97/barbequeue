<?php

declare(strict_types=1);

namespace App\Service\Notification;

use App\Entity\NotificationSettings;
use App\Enum\NotificationMode;
use App\Enum\NotificationSetting;
use App\Repository\WorkspaceRepositoryInterface;
use App\Resolver\UserResolver;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;

readonly class NotificationSettingsManager
{
    public function __construct(
        private WorkspaceRepositoryInterface $workspaceRepository,
        private UserResolver $userResolver,
        private EntityManagerInterface $entityManager,
    ) {
    }

    /** @throws EntityNotFoundException */
    public function updatePreferences(
        string $userId,
        ?string $userName,
        string $teamId,
        array $enabledNotifications,
        string $mode,
    ): void {
        $workspace = $this->workspaceRepository->findOneBy([
            'slackId' => $teamId,
        ]);

        if (null === $workspace) {
            throw new EntityNotFoundException('Workspace not found.');
        }

        $user = $this->userResolver->resolve($userId, $workspace);

        $user->setName($userName);

        $settings = $user->getNotificationSettings() ?? new NotificationSettings();

        $settings->setMode(NotificationMode::tryFrom($mode) ?? NotificationMode::ALWAYS_NOTIFY);

        $settingsArray = [];

        foreach (NotificationSetting::cases() as $setting) {
            if (!in_array($setting->value, $enabledNotifications, true)) {
                $settingsArray[$setting->value] = false;
            }
        }

        $settings->setSettings($settingsArray);

        $user->setNotificationSettings($settings);

        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }
}

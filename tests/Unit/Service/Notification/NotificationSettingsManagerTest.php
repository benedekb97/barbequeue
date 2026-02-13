<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Notification;

use App\Entity\NotificationSettings;
use App\Entity\User;
use App\Entity\Workspace;
use App\Enum\NotificationMode;
use App\Enum\NotificationSetting;
use App\Repository\WorkspaceRepositoryInterface;
use App\Resolver\UserResolver;
use App\Service\Notification\NotificationSettingsManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(NotificationSettingsManager::class)]
class NotificationSettingsManagerTest extends KernelTestCase
{
    #[Test]
    public function itShouldThrowEntityNotFoundExceptionIfWorkspaceCouldNotBeFound(): void
    {
        $workspaceRepository = $this->createMock(WorkspaceRepositoryInterface::class);
        $workspaceRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['slackId' => $teamId = 'teamId'])
            ->willReturn(null);

        $this->expectException(EntityNotFoundException::class);

        $manager = new NotificationSettingsManager(
            $workspaceRepository,
            $this->createStub(UserResolver::class),
            $this->createStub(EntityManagerInterface::class),
        );

        $manager->updatePreferences('userId', null, $teamId, [], 'mode');
    }

    #[Test, DataProvider('provideModes')]
    public function itShouldSaveNotificationSettings(string $mode, NotificationMode $expectedMode): void
    {
        $workspaceRepository = $this->createMock(WorkspaceRepositoryInterface::class);
        $workspaceRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['slackId' => $teamId = 'teamId'])
            ->willReturn($workspace = $this->createStub(Workspace::class));

        $userResolver = $this->createMock(UserResolver::class);
        $userResolver->expects($this->once())
            ->method('resolve')
            ->with($userId = 'userId', $workspace)
            ->willReturn($user = $this->createMock(User::class));

        $user->expects($this->once())
            ->method('getNotificationSettings')
            ->willReturn($settings = $this->createMock(NotificationSettings::class));

        $user->expects($this->once())
            ->method('setNotificationSettings')
            ->with($settings)
            ->willReturnSelf();

        $user->expects($this->once())
            ->method('setName')
            ->with($userName = 'userName')
            ->willReturnSelf();

        $settings->expects($this->once())
            ->method('setMode')
            ->with($expectedMode);

        $settings->expects($this->once())
            ->method('setSettings')
            ->with([
                NotificationSetting::DEPLOYMENT_CANCELLED->value => false,
                NotificationSetting::THIRD_PARTY_DEPLOYMENT_STARTED->value => false,
                NotificationSetting::DEPLOYMENT_COMPLETED->value => false,
                NotificationSetting::DEPLOYMENT_STARTED->value => false,
                NotificationSetting::THIRD_PARTY_DEPLOYMENT_ADDED->value => false,
            ]);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->once())
            ->method('persist')
            ->with($user);

        $entityManager->expects($this->once())
            ->method('flush');

        $manager = new NotificationSettingsManager($workspaceRepository, $userResolver, $entityManager);

        $manager->updatePreferences($userId, $userName, $teamId, [
            NotificationSetting::THIRD_PARTY_DEPLOYMENT_COMPLETED->value,
            NotificationSetting::THIRD_PARTY_DEPLOYMENT_CANCELLED->value,
        ], $mode);
    }

    public static function provideModes(): array
    {
        return [
            ['only-when-active', NotificationMode::ONLY_WHEN_ACTIVE],
            ['always-notify', NotificationMode::ALWAYS_NOTIFY],
            ['not-an-actual-mode', NotificationMode::ALWAYS_NOTIFY],
        ];
    }
}

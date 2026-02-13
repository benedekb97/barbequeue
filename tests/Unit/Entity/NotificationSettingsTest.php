<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\NotificationSettings;
use App\Entity\User;
use App\Enum\NotificationMode;
use App\Enum\NotificationSetting;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(NotificationSettings::class)]
class NotificationSettingsTest extends KernelTestCase
{
    #[Test]
    public function itShouldReturnTrueForAllFlagsByDefault(): void
    {
        $settings = new NotificationSettings();

        $this->assertEquals(NotificationMode::ALWAYS_NOTIFY, $settings->getMode());

        foreach (NotificationSetting::cases() as $setting) {
            $this->assertTrue($settings->isSettingEnabled($setting));
        }
    }

    #[Test]
    public function itShouldSetValuesCorrectly(): void
    {
        $settings = new NotificationSettings();

        $this->assertNull($settings->getId());

        $user = $this->createMock(User::class);
        $user->expects($this->once())
            ->method('setNotificationSettings')
            ->with($settings)
            ->willReturnSelf();

        $settings->setUser($user)
            ->setMode($mode = NotificationMode::ONLY_WHEN_ACTIVE);

        $settings->setSetting(NotificationSetting::DEPLOYMENT_COMPLETED, false);

        $this->assertFalse($settings->isSettingEnabled(NotificationSetting::DEPLOYMENT_COMPLETED));

        $this->assertSame($user, $settings->getUser());
        $this->assertSame($mode, $settings->getMode());

        $this->assertEquals([
            NotificationSetting::DEPLOYMENT_COMPLETED->value => false,
        ], $settings->getSettings());

        $settings->setSettings($settingsArray = [
            NotificationSetting::THIRD_PARTY_DEPLOYMENT_STARTED->value => false,
        ]);

        $this->assertEquals($settingsArray, $settings->getSettings());

        $this->assertFalse($settings->isSettingEnabled(NotificationSetting::THIRD_PARTY_DEPLOYMENT_STARTED));
        $this->assertTrue($settings->isSettingEnabled(NotificationSetting::DEPLOYMENT_COMPLETED));
    }
}

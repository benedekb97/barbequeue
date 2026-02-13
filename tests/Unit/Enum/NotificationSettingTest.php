<?php

declare(strict_types=1);

namespace App\Tests\Unit\Enum;

use App\Enum\NotificationSetting;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(NotificationSetting::class)]
class NotificationSettingTest extends KernelTestCase
{
    #[Test]
    public function itShouldReturnCorrectThirdPartySettings(): void
    {
        $result = NotificationSetting::getThirdPartySettings();

        $this->assertCount(4, $result);

        $this->assertContains(NotificationSetting::THIRD_PARTY_DEPLOYMENT_STARTED, $result);
        $this->assertContains(NotificationSetting::THIRD_PARTY_DEPLOYMENT_ADDED, $result);
        $this->assertContains(NotificationSetting::THIRD_PARTY_DEPLOYMENT_CANCELLED, $result);
        $this->assertContains(NotificationSetting::THIRD_PARTY_DEPLOYMENT_COMPLETED, $result);
    }

    #[Test]
    public function itShouldReturnCorrectUserSettings(): void
    {
        $result = NotificationSetting::getUserSettings();

        $this->assertCount(3, $result);

        $this->assertContains(NotificationSetting::DEPLOYMENT_STARTED, $result);
        $this->assertContains(NotificationSetting::DEPLOYMENT_CANCELLED, $result);
        $this->assertContains(NotificationSetting::DEPLOYMENT_COMPLETED, $result);
    }

    #[Test, DataProvider('provideNames')]
    public function itShouldReturnCorrectNames(NotificationSetting $setting, string $expectedName): void
    {
        $this->assertEquals($expectedName, $setting->getName());
    }

    public static function provideNames(): array
    {
        return [
            [NotificationSetting::DEPLOYMENT_STARTED, 'When a deployment is started'],
            [NotificationSetting::THIRD_PARTY_DEPLOYMENT_STARTED, 'When a deployment is started'],
            [NotificationSetting::DEPLOYMENT_COMPLETED, 'When a deployment is completed'],
            [NotificationSetting::THIRD_PARTY_DEPLOYMENT_COMPLETED, 'When a deployment is completed'],
            [NotificationSetting::DEPLOYMENT_CANCELLED, 'When a deployment is cancelled'],
            [NotificationSetting::THIRD_PARTY_DEPLOYMENT_CANCELLED, 'When a deployment is cancelled'],
            [NotificationSetting::THIRD_PARTY_DEPLOYMENT_ADDED, 'When a deployment is added to a queue'],
        ];
    }
}

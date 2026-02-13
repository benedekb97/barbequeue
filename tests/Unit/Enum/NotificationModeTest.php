<?php

declare(strict_types=1);

namespace App\Tests\Unit\Enum;

use App\Enum\NotificationMode;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(NotificationMode::class)]
class NotificationModeTest extends KernelTestCase
{
    #[Test, DataProvider('provideNames')]
    public function itShouldReturnCorrectName(NotificationMode $mode, string $expectedName): void
    {
        $this->assertEquals($expectedName, $mode->getName());
    }

    public static function provideNames(): array
    {
        return [
            [NotificationMode::ALWAYS_NOTIFY, 'Always send notifications'],
            [NotificationMode::ONLY_WHEN_ACTIVE, 'Only send notifications when I\'m active'],
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Unit\DataFixtures\Queue;

use App\DataFixtures\Queue\Queues;
use App\DataFixtures\Queue\Workspaces;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(Queues::class)]
class QueuesTest extends KernelTestCase
{
    #[Test]
    public function itShouldHaveFiveCases(): void
    {
        $this->assertCount(5, Queues::cases());

        foreach (Queues::cases() as $case) {
            $this->assertContains($case, [
                Queues::NO_EXPIRY_USER_LIMIT_ONE,
                Queues::FIFTEEN_MINUTE_EXPIRY_NO_USER_LIMIT,
                Queues::FIFTEEN_MINUTE_EXPIRY_USER_LIMIT_THREE,
                Queues::NO_EXPIRY_USER_LIMIT_THREE,
                Queues::NO_EXPIRY_NO_USER_LIMIT,
            ]);
        }
    }

    #[Test, DataProvider('provideForItShouldReturnTheCorrectData')]
    public function itShouldReturnTheCorrectData(
        Queues $case,
        Workspaces $teamId,
        ?int $expiry,
        ?int $maximumEntries,
        int $initialUserCount,
    ): void {
        $this->assertEquals($teamId, $case->getTeamId());
        $this->assertEquals($expiry, $case->getExpiryMinutes());
        $this->assertEquals($maximumEntries, $case->getMaximumEntriesPerUser());
        $this->assertEquals($initialUserCount, $case->getInitialUserCount());
    }

    public static function provideForItShouldReturnTheCorrectData(): array
    {
        return [
            [Queues::NO_EXPIRY_NO_USER_LIMIT, Workspaces::FIRST, null, null, 3],
            [Queues::FIFTEEN_MINUTE_EXPIRY_USER_LIMIT_THREE, Workspaces::FIRST, 15, 3, 3],
            [Queues::FIFTEEN_MINUTE_EXPIRY_NO_USER_LIMIT, Workspaces::FIRST, 15, null, 4],
            [Queues::NO_EXPIRY_USER_LIMIT_THREE, Workspaces::SECOND, null, 3, 3],
            [Queues::NO_EXPIRY_USER_LIMIT_ONE, Workspaces::SECOND, null, 1, 3],
        ];
    }
}

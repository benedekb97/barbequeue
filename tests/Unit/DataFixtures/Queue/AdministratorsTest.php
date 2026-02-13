<?php

declare(strict_types=1);

namespace App\Tests\Unit\DataFixtures\Queue;

use App\DataFixtures\Queue\Administrators;
use App\DataFixtures\Queue\Workspaces;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(Administrators::class)]
class AdministratorsTest extends KernelTestCase
{
    #[Test]
    public function itShouldHaveTwoCases(): void
    {
        $this->assertCount(2, Administrators::cases());
    }

    #[Test, DataProvider('provideCases')]
    public function itShouldAlwaysReturnFirstDomain(Administrators $case): void
    {
        $this->assertEquals(Workspaces::FIRST, $case->getTeamId());
    }

    public static function provideCases(): iterable
    {
        foreach (Administrators::cases() as $case) {
            yield [$case];
        }
    }
}

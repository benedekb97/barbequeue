<?php

declare(strict_types=1);

namespace App\Tests\Unit\DataFixtures\Queue;

use App\DataFixtures\Queue\Administrators;
use App\DataFixtures\Queue\Queues;
use App\DataFixtures\Queue\Repositories;
use App\DataFixtures\Queue\Workspaces;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(Workspaces::class)]
class WorkspacesTest extends KernelTestCase
{
    #[Test]
    public function itShouldHaveTwoCases(): void
    {
        $this->assertCount(2, Workspaces::cases());

        foreach (Workspaces::cases() as $case) {
            $this->assertContains($case, [Workspaces::FIRST, Workspaces::SECOND]);
        }
    }

    #[Test, DataProvider('provideTeamIds')]
    public function itShouldReturnCorrectName(Workspaces $teamId): void
    {
        $this->assertEquals($teamId->value, $teamId->getName());
        $this->assertEquals('bot-token', $teamId->getBotToken());
    }

    public static function provideTeamIds(): array
    {
        return [
            [Workspaces::FIRST],
            [Workspaces::SECOND],
        ];
    }

    #[Test, DataProvider('provideTeamIdQueues')]
    public function itShouldReturnCorrectQueues(Workspaces $teamId, array $expectedQueues): void
    {
        $this->assertEquals($expectedQueues, $teamId->getQueues());
    }

    public static function provideTeamIdQueues(): array
    {
        return [
            [Workspaces::FIRST, [
                Queues::NO_EXPIRY_NO_USER_LIMIT,
                Queues::FIFTEEN_MINUTE_EXPIRY_NO_USER_LIMIT,
                Queues::FIFTEEN_MINUTE_EXPIRY_USER_LIMIT_THREE,
            ]],
            [Workspaces::SECOND, [
                Queues::NO_EXPIRY_USER_LIMIT_THREE,
                Queues::NO_EXPIRY_USER_LIMIT_ONE,
            ]],
        ];
    }

    #[Test, DataProvider('provideTeamIdAdministrators')]
    public function itShouldReturnCorrectAdministrators(Workspaces $teamId, array $administrators): void
    {
        $this->assertEquals($administrators, $teamId->getAdministrators());
    }

    public static function provideTeamIdAdministrators(): array
    {
        return [
            [Workspaces::FIRST, Administrators::cases()],
            [Workspaces::SECOND, []],
        ];
    }

    #[Test, DataProvider('provideWorkspaceRepositories')]
    public function itShouldReturnCorrectRepositories(Workspaces $workspace, array $repositories): void
    {
        $this->assertEquals($repositories, $workspace->getRepositories());
    }

    public static function provideWorkspaceRepositories(): array
    {
        return [
            [Workspaces::FIRST, [Repositories::REPOSITORY_A, Repositories::REPOSITORY_B]],
            [Workspaces::SECOND, []],
        ];
    }
}

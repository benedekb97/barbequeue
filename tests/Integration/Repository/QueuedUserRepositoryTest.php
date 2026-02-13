<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository;

use App\DataFixtures\Queue\Workspaces;
use App\Entity\QueuedUser;
use App\Entity\Workspace;
use App\Repository\QueuedUserRepository;
use App\Repository\QueuedUserRepositoryInterface;
use App\Repository\WorkspaceRepositoryInterface;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(QueuedUserRepository::class)]
class QueuedUserRepositoryTest extends KernelTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        self::bootKernel();
    }

    #[Test]
    public function itShouldFindAllExpiredUsers(): void
    {
        /** @var QueuedUserRepositoryInterface $repository */
        $repository = static::getContainer()->get(QueuedUserRepositoryInterface::class);

        $result = $repository->findAllExpired();

        $this->assertNotEmpty($result);
        $this->assertCount(1, $result);
        $this->assertInstanceOf(QueuedUser::class, $result[0]);
    }

    #[Test]
    public function itShouldFindQueuedUserByIdQueueNameAndWorkspace(): void
    {
        /** @var QueuedUserRepositoryInterface $repository */
        $repository = static::getContainer()->get(QueuedUserRepositoryInterface::class);

        $queuedUsers = $repository->findAll();

        /** @var QueuedUser $queuedUser */
        $queuedUser = $queuedUsers[array_rand($queuedUsers)];

        $result = $repository->findOneByIdQueueNameAndWorkspace(
            (int) $queuedUser->getId(),
            (string) $queuedUser->getQueue()?->getName(),
            $queuedUser->getQueue()?->getWorkspace(),
        );

        $this->assertNotNull($result);
    }

    #[Test, DataProvider('provideParametersForCount')]
    public function itShouldReturnCorrectCountForValues(bool $active, bool $uniqueUsers, int $expectedCount): void
    {
        /** @var QueuedUserRepositoryInterface $repository */
        $repository = static::getContainer()->get(QueuedUserRepositoryInterface::class);

        /** @var WorkspaceRepositoryInterface $workspaceRepository */
        $workspaceRepository = static::getContainer()->get(WorkspaceRepositoryInterface::class);

        /** @var Workspace $workspace */
        $workspace = $workspaceRepository->findOneBy(['slackId' => Workspaces::FIRST->value]);

        $result = $repository->countForWorkspace(
            $workspace,
            CarbonImmutable::yesterday(),
            CarbonImmutable::now(),
            $active,
            $uniqueUsers
        );

        $this->assertEquals($expectedCount, $result);
    }

    public static function provideParametersForCount(): array
    {
        return [
            [true, true, 2],
            [false, true, 2],
            [true, false, 10],
            [false, false, 10],
        ];
    }
}

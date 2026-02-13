<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository;

use App\DataFixtures\Queue\Repositories;
use App\DataFixtures\Queue\Workspaces;
use App\Entity\Repository;
use App\Entity\Workspace;
use App\Repository\RepositoryRepository;
use App\Repository\RepositoryRepositoryInterface;
use App\Repository\WorkspaceRepository;
use App\Repository\WorkspaceRepositoryInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(RepositoryRepository::class)]
class RepositoryRepositoryTest extends KernelTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        self::bootKernel();
    }

    #[Test]
    public function itShouldReturnNullIfNoRepositoryExistsWithNameOnWorkspace(): void
    {
        /** @var RepositoryRepository $repository */
        $repository = static::getContainer()->get(RepositoryRepository::class);

        /** @var WorkspaceRepository $workspaceRepository */
        $workspaceRepository = static::getContainer()->get(WorkspaceRepository::class);

        $workspace = $workspaceRepository->findOneBy(['slackId' => Workspaces::FIRST->value]);

        $this->assertNotNull($workspace);

        $result = $repository->findOneByNameAndWorkspace('non-existent-repository', $workspace);

        $this->assertNull($result);
    }

    #[Test]
    public function itShouldReturnNullIfRepositoryExistsButOnDifferentWorkspace(): void
    {
        /** @var RepositoryRepository $repository */
        $repository = static::getContainer()->get(RepositoryRepository::class);

        /** @var WorkspaceRepository $workspaceRepository */
        $workspaceRepository = static::getContainer()->get(WorkspaceRepository::class);

        $workspace = $workspaceRepository->findOneBy(['slackId' => Workspaces::SECOND->value]);

        $this->assertNotNull($workspace);

        $result = $repository->findOneByNameAndWorkspace(Repositories::REPOSITORY_A->value, $workspace);

        $this->assertNull($result);
    }

    #[Test]
    public function itShouldReturnRepository(): void
    {
        /** @var RepositoryRepository $repository */
        $repository = static::getContainer()->get(RepositoryRepository::class);

        /** @var WorkspaceRepository $workspaceRepository */
        $workspaceRepository = static::getContainer()->get(WorkspaceRepository::class);

        $workspace = $workspaceRepository->findOneBy(['slackId' => Workspaces::FIRST->value]);

        $this->assertNotNull($workspace);

        $result = $repository->findOneByNameAndWorkspace(Repositories::REPOSITORY_A->value, $workspace);

        $this->assertInstanceOf(Repository::class, $result);
        $this->assertEquals(Repositories::REPOSITORY_A->value, $result->getName());
        $this->assertEquals(Repositories::REPOSITORY_A->getUrl(), $result->getUrl());
    }

    #[Test]
    public function itShouldReturnRepositoryOnFindOneByNameAndTeamIdIfExists(): void
    {
        /** @var RepositoryRepositoryInterface $repository */
        $repository = static::getContainer()->get(RepositoryRepository::class);

        $result = $repository->findOneByNameAndTeamid(Repositories::REPOSITORY_A->value, Workspaces::FIRST->value);

        $this->assertInstanceOf(Repository::class, $result);
    }

    #[Test]
    public function itShouldReturnRepositoriesForWorkspace(): void
    {
        /** @var RepositoryRepositoryInterface $repository */
        $repository = static::getContainer()->get(RepositoryRepository::class);

        $result = $repository->findByTeamId(Workspaces::FIRST->value);

        $this->assertCount(count(Workspaces::FIRST->getRepositories()), $result);
    }

    #[Test]
    public function itShouldReturnRepositoryByIdAndWorkspace(): void
    {
        /** @var RepositoryRepositoryInterface $repository */
        $repository = static::getContainer()->get(RepositoryRepository::class);

        $initialResult = $repository->findOneByNameAndTeamid(Repositories::REPOSITORY_A->value, Workspaces::FIRST->value);

        $this->assertInstanceOf(Repository::class, $initialResult);

        /** @var WorkspaceRepositoryInterface $workspaceRepository */
        $workspaceRepository = static::getContainer()->get(WorkspaceRepository::class);

        $workspace = $workspaceRepository->findOneBy([
            'slackId' => Workspaces::FIRST->value,
        ]);

        $this->assertInstanceOf(Workspace::class, $workspace);

        $result = $repository->findOneByIdAndWorkspace($initialResult->getId() ?? 1, $workspace);

        $this->assertSame($initialResult, $result);
    }

    #[Test]
    public function itShouldReturnMultipleIfFoundByIds(): void
    {
        /** @var RepositoryRepositoryInterface $repository */
        $repository = static::getContainer()->get(RepositoryRepositoryInterface::class);

        /** @var Repository[] $initialResult */
        $initialResult = $repository->findByTeamId(Workspaces::FIRST->value);

        /** @var WorkspaceRepositoryInterface $workspaceRepository */
        $workspaceRepository = static::getContainer()->get(WorkspaceRepositoryInterface::class);

        /** @var Workspace $workspace */
        $workspace = $workspaceRepository->findOneBy([
            'slackId' => Workspaces::FIRST->value,
        ]);

        /** @var int[] $repositoryIds */
        $repositoryIds = array_map(function (Repository $repository) {
            return $repository->getId();
        }, $initialResult);

        $repositories = $repository->findByIdsAndWorkspace($repositoryIds, $workspace);

        $this->assertEquals($initialResult, $repositories);
    }

    #[Test]
    public function itShouldReturnEmptyArrayIfNoRepositoryIdsPassed(): void
    {
        /** @var RepositoryRepositoryInterface $repository */
        $repository = static::getContainer()->get(RepositoryRepositoryInterface::class);

        $result = $repository->findByIdsAndWorkspace(null, $this->createStub(Workspace::class));

        $this->assertEmpty($result);
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Repository;

use App\Entity\Repository;
use App\Entity\Workspace;
use App\Repository\RepositoryRepositoryInterface;
use App\Service\Administrator\Exception\UnauthorisedException;
use App\Service\Repository\Exception\RepositoryAlreadyExistsException;
use App\Service\Repository\Exception\RepositoryNotFoundException;
use App\Service\Repository\RepositoryManager;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(RepositoryManager::class)]
class RepositoryManagerTest extends KernelTestCase
{
    #[Test]
    public function itShouldThrowUnauthorisedExceptionIfWorkspaceIsNull(): void
    {
        $manager = new RepositoryManager(
            $this->createStub(RepositoryRepositoryInterface::class),
            $this->createStub(EntityManagerInterface::class),
        );

        $this->expectException(UnauthorisedException::class);

        $manager->addRepository('name', null, [], null);
    }

    #[Test]
    public function itShouldThrowRepositoryAlreadyExistsExceptionIfRepositoryReturnsValue(): void
    {
        $repository = $this->createMock(RepositoryRepositoryInterface::class);
        $repository->expects($this->once())
            ->method('findOneByNameAndWorkspace')
            ->with($name = 'name', $workspace = $this->createStub(Workspace::class))
            ->willReturn($this->createStub(Repository::class));

        $this->expectException(RepositoryAlreadyExistsException::class);

        $manager = new RepositoryManager(
            $repository,
            $this->createStub(EntityManagerInterface::class),
        );

        $manager->addRepository($name, null, [], $workspace);
    }

    #[Test]
    public function itShouldPersistRepository(): void
    {
        $addedRepository = null;

        $name = 'name';
        $url = 'url';

        $entityManager = $this->createMock(EntityManagerInterface::class);

        $blocksRepository = $this->createMock(Repository::class);

        $workspace = $this->createMock(Workspace::class);
        $workspace->expects($this->once())
            ->method('addRepository')
            ->willReturnCallback(function ($argument) use (&$addedRepository, $name, $url, $workspace, $entityManager, $blocksRepository) {
                $this->assertInstanceOf(Repository::class, $argument);
                $this->assertEquals($name, $argument->getName());
                $this->assertEquals($url, $argument->getUrl());

                $entityManager->expects($this->once())
                    ->method('persist')
                    ->with($argument);

                $blocksRepository->expects($this->once())
                    ->method('addBlockedByDeployment')
                    ->with($argument)
                    ->willReturnSelf();

                $addedRepository = $argument;

                return $workspace;
            });

        $repositoryRepository = $this->createMock(RepositoryRepositoryInterface::class);
        $repositoryRepository->expects($this->once())
            ->method('findOneByNameAndWorkspace')
            ->with($name, $workspace)
            ->willReturn(null);

        $repositoryRepository->expects($this->once())
            ->method('findByIdsAndWorkspace')
            ->with($ids = [1], $workspace)
            ->willReturn([$blocksRepository]);

        $entityManager->expects($this->once())
            ->method('flush');

        $manager = new RepositoryManager($repositoryRepository, $entityManager);

        $result = $manager->addRepository($name, $url, $ids, $workspace);

        $this->assertSame($addedRepository, $result);
    }

    #[Test]
    public function itShouldThrowUnauthorisedExceptionIfWorkspaceIsNullOnEdit(): void
    {
        $manager = new RepositoryManager(
            $this->createStub(RepositoryRepositoryInterface::class),
            $this->createStub(EntityManagerInterface::class),
        );

        $this->expectException(UnauthorisedException::class);

        $manager->editRepository(null, null, null, [], null);
    }

    #[Test]
    public function itShouldThrowUnauthorisedExceptionIfNameIsNullOnEdit(): void
    {
        $manager = new RepositoryManager(
            $this->createStub(RepositoryRepositoryInterface::class),
            $this->createStub(EntityManagerInterface::class),
        );

        $this->expectException(UnauthorisedException::class);

        $manager->editRepository(null, null, null, [], $this->createStub(Workspace::class));
    }

    #[Test]
    public function itShouldThrowRepositoryNotFoundExceptionIfIdIsNull(): void
    {
        $manager = new RepositoryManager(
            $this->createStub(RepositoryRepositoryInterface::class),
            $this->createStub(EntityManagerInterface::class),
        );

        $this->expectException(RepositoryNotFoundException::class);

        $manager->editRepository(null, 'name', null, [], $this->createStub(Workspace::class));
    }

    #[Test]
    public function itShouldThrowRepositoryNotFoundExceptionIfRepositoryReturnsNull(): void
    {
        $repository = $this->createMock(RepositoryRepositoryInterface::class);
        $repository->expects($this->once())
            ->method('findOneByIdAndWorkspace')
            ->with($id = 1, $workspace = $this->createStub(Workspace::class))
            ->willReturn(null);

        $manager = new RepositoryManager(
            $repository,
            $this->createStub(EntityManagerInterface::class),
        );

        $this->expectException(RepositoryNotFoundException::class);

        $manager->editRepository($id, 'name', null, [], $workspace);
    }

    #[Test]
    public function itShouldThrowRepositoryAlreadyExistsExceptionIfRepositoryFindsAnotherRepositoryWithSameName(): void
    {
        $repository = $this->createMock(RepositoryRepositoryInterface::class);
        $repository->expects($this->once())
            ->method('findOneByIdAndWorkspace')
            ->with($id = 1, $workspace = $this->createStub(Workspace::class))
            ->willReturn($this->createStub(Repository::class));

        $repository->expects($this->once())
            ->method('findOneByNameAndWorkspace')
            ->with($name = 'name', $workspace)
            ->willReturn($this->createStub(Repository::class));

        $manager = new RepositoryManager(
            $repository,
            $this->createStub(EntityManagerInterface::class),
        );

        $this->expectException(RepositoryAlreadyExistsException::class);

        $manager->editRepository($id, $name, null, [], $workspace);
    }

    #[Test]
    public function itShouldPersistEditedRepository(): void
    {
        $repository = $this->createMock(Repository::class);
        $repository->expects($this->once())
            ->method('setName')
            ->with($name = 'name')
            ->willReturnSelf();

        $repository->expects($this->once())
            ->method('setUrl')
            ->with($url = 'url')
            ->willReturnSelf();

        $repository->expects($this->once())
            ->method('clearDeploymentBlocksRepositories')
            ->willReturnSelf();

        $repositoryRepository = $this->createMock(RepositoryRepositoryInterface::class);
        $repositoryRepository->expects($this->once())
            ->method('findOneByIdAndWorkspace')
            ->with($id = 1, $workspace = $this->createStub(Workspace::class))
            ->willReturn($repository);

        $blockedByRepository = $this->createStub(Repository::class);

        $repository->expects($this->once())
            ->method('addDeploymentBlocksRepository')
            ->with($blockedByRepository)
            ->willReturnSelf();

        $repositoryRepository->expects($this->once())
            ->method('findByIdsAndWorkspace')
            ->with($ids = [1], $workspace)
            ->willReturn([$blockedByRepository]);

        $repositoryRepository->expects($this->once())
            ->method('findOneByNameAndWorkspace')
            ->with($name, $workspace)
            ->willReturn(null);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->once())
            ->method('persist')
            ->with($repository);

        $entityManager->expects($this->once())
            ->method('flush');

        $manager = new RepositoryManager(
            $repositoryRepository,
            $entityManager,
        );

        $result = $manager->editRepository($id, $name, $url, $ids, $workspace);

        $this->assertSame($repository, $result);
    }

    #[Test]
    public function itShouldThrowUnauthorisedExceptionIfWorkspaceIsNullOnRemove(): void
    {
        $manager = new RepositoryManager(
            $this->createStub(RepositoryRepositoryInterface::class),
            $this->createStub(EntityManagerInterface::class),
        );

        $this->expectException(UnauthorisedException::class);

        $manager->removeRepository(1, null);
    }

    #[Test]
    public function itShouldThrowRepositoryNotFoundExceptionIfRepositoryReturnsNullOnRemove(): void
    {
        $repository = $this->createMock(RepositoryRepositoryInterface::class);
        $repository->expects($this->once())
            ->method('findOneByIdAndWorkspace')
            ->with($id = 1, $workspace = $this->createStub(Workspace::class))
            ->willReturn(null);

        $manager = new RepositoryManager(
            $repository,
            $this->createStub(EntityManagerInterface::class),
        );

        $this->expectException(RepositoryNotFoundException::class);

        $manager->removeRepository($id, $workspace);
    }

    #[Test]
    public function itShouldRemoveAndFlushRepositoryOnRemove(): void
    {
        $repository = $this->createMock(Repository::class);
        $repository->expects($this->once())
            ->method('getName')
            ->willReturn($name = 'name');

        $repository->expects($this->once())
            ->method('clearDeploymentBlocksRepositories')
            ->willReturnSelf();

        $repositoryRepository = $this->createMock(RepositoryRepositoryInterface::class);
        $repositoryRepository->expects($this->once())
            ->method('findOneByIdAndWorkspace')
            ->with($id = 1, $workspace = $this->createStub(Workspace::class))
            ->willReturn($repository);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->once())
            ->method('remove')
            ->with($repository);

        $entityManager->expects($this->once())
            ->method('flush');

        $manager = new RepositoryManager(
            $repositoryRepository,
            $entityManager,
        );

        $result = $manager->removeRepository($id, $workspace);

        $this->assertSame($name, $result);
    }
}

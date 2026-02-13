<?php

declare(strict_types=1);

namespace App\Service\Repository;

use App\Entity\Repository;
use App\Entity\Workspace;
use App\Repository\RepositoryRepositoryInterface;
use App\Service\Administrator\Exception\UnauthorisedException;
use App\Service\Repository\Exception\RepositoryAlreadyExistsException;
use App\Service\Repository\Exception\RepositoryNotFoundException;
use Doctrine\ORM\EntityManagerInterface;

readonly class RepositoryManager
{
    public function __construct(
        private RepositoryRepositoryInterface $repositoryRepository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @param int[]|null $deploymentBlocksRepositories
     *
     * @throws UnauthorisedException|RepositoryAlreadyExistsException
     */
    public function addRepository(string $name, ?string $url, ?array $deploymentBlocksRepositories, ?Workspace $workspace): Repository
    {
        if (null === $workspace) {
            throw new UnauthorisedException();
        }

        $repository = $this->repositoryRepository->findOneByNameAndWorkspace($name, $workspace);

        if ($repository instanceof Repository) {
            throw new RepositoryAlreadyExistsException($name);
        }

        $blocksRepositories = $this->repositoryRepository->findByIdsAndWorkspace($deploymentBlocksRepositories, $workspace);

        $repository = new Repository()
            ->setName($name)
            ->setUrl($url);

        $workspace->addRepository($repository);

        foreach ($blocksRepositories as $blocksRepository) {
            $repository->addDeploymentBlocksRepository($blocksRepository);
        }

        $this->entityManager->persist($repository);
        $this->entityManager->flush();

        return $repository;
    }

    /**
     * @param int[]|null $blocksDeploymentForRepositories
     *
     * @throws UnauthorisedException|RepositoryNotFoundException|RepositoryAlreadyExistsException
     */
    public function editRepository(
        ?int $id,
        ?string $name,
        ?string $url,
        ?array $blocksDeploymentForRepositories,
        ?Workspace $workspace,
    ): Repository {
        if (null === $workspace) {
            throw new UnauthorisedException();
        }

        if (null === $name) {
            throw new UnauthorisedException();
        }

        if (null === $id) {
            throw new RepositoryNotFoundException();
        }

        $repository = $this->repositoryRepository->findOneByIdAndWorkspace($id, $workspace);

        if (!$repository instanceof Repository) {
            throw new RepositoryNotFoundException();
        }

        $existingRepository = $this->repositoryRepository->findOneByNameAndWorkspace($name, $workspace);

        if ($existingRepository instanceof Repository && $existingRepository !== $repository) {
            throw new RepositoryAlreadyExistsException($name);
        }

        $blockedRepositories = $this->repositoryRepository->findByIdsAndWorkspace($blocksDeploymentForRepositories, $workspace);

        $repository->setName($name)->setUrl($url)->clearDeploymentBlocksRepositories();

        foreach ($blockedRepositories as $blockedRepository) {
            $repository->addDeploymentBlocksRepository($blockedRepository);
        }

        $this->entityManager->persist($repository);
        $this->entityManager->flush();

        return $repository;
    }

    /** @throws UnauthorisedException|RepositoryNotFoundException */
    public function removeRepository(int $id, ?Workspace $workspace): ?string
    {
        if (null === $workspace) {
            throw new UnauthorisedException();
        }

        $repository = $this->repositoryRepository->findOneByIdAndWorkspace($id, $workspace);

        if (null === $repository) {
            throw new RepositoryNotFoundException();
        }

        $name = $repository->getName();

        $repository->clearDeploymentBlocksRepositories();

        $this->entityManager->remove($repository);
        $this->entityManager->flush();

        return $name;
    }
}

<?php

declare(strict_types=1);

namespace App\Service\Administrator;

use App\Entity\Administrator;
use App\Repository\AdministratorRepositoryInterface;
use App\Resolver\UserResolver;
use App\Service\Administrator\Exception\AdministratorExistsException;
use App\Service\Administrator\Exception\AdministratorNotFoundException;
use App\Service\Administrator\Exception\UnauthorisedException;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

readonly class AdministratorManager
{
    public function __construct(
        private AdministratorRepositoryInterface $administratorRepository,
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger,
        private UserResolver $userResolver,
    ) {
    }

    /** @throws UnauthorisedException|AdministratorExistsException */
    public function addUser(string $userId, string $teamId, ?Administrator $addedBy): Administrator
    {
        if (null === $addedBy) {
            $this->logger->error('Received null administrator. Make sure you extended AbstractAuthenticatedCommandHandler or AbstractAuthenticatedInteractionHandler');

            throw new UnauthorisedException();
        }

        if ($addedBy->getWorkspace()?->getSlackId() !== $teamId) {
            throw new UnauthorisedException();
        }

        $existingAdministrator = $this->administratorRepository->findOneByUserIdAndWorkspace(
            $userId,
            $workspace = $addedBy->getWorkspace()
        );

        if ($existingAdministrator instanceof Administrator) {
            throw new AdministratorExistsException($existingAdministrator);
        }

        $user = $this->userResolver->resolve($userId, $workspace);

        $administrator = new Administrator()
            ->setUser($user)
            ->setWorkspace($workspace)
            ->setAddedBy($addedBy);

        $this->entityManager->persist($administrator);
        $this->entityManager->flush();

        return $administrator;
    }

    /** @throws UnauthorisedException|AdministratorNotFoundException */
    public function removeUser(string $userId, string $teamId, ?Administrator $removedBy): void
    {
        if (null === $removedBy) {
            throw new UnauthorisedException();
        }

        if ($removedBy->getWorkspace()?->getSlackId() !== $teamId) {
            throw new UnauthorisedException();
        }

        $administrator = $this->administratorRepository->findOneByUserIdAndWorkspace(
            $userId,
            $workspace = $removedBy->getWorkspace()
        );

        if (null === $administrator) {
            throw new AdministratorNotFoundException($userId);
        }

        if (!$administrator->isAddedBy($removedBy)) {
            throw new UnauthorisedException();
        }

        $workspace->removeAdministrator($administrator);

        $this->entityManager->remove($administrator);
        $this->entityManager->flush();
    }
}

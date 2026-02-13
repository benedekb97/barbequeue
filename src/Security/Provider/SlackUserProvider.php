<?php

declare(strict_types=1);

namespace App\Security\Provider;

use App\Entity\User;
use App\Repository\UserRepositoryInterface;
use App\Repository\WorkspaceRepositoryInterface;
use App\Resolver\UserResolver;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\AttributesBasedUserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/** @implements AttributesBasedUserProviderInterface<User> */
readonly class SlackUserProvider implements AttributesBasedUserProviderInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserRepositoryInterface $userRepository,
        private WorkspaceRepositoryInterface $workspaceRepository,
        private UserResolver $userResolver,
    ) {
    }

    public function refreshUser(UserInterface $user): User
    {
        $this->entityManager->refresh($user);

        if ($user instanceof User) {
            return $user;
        }

        throw new UnsupportedUserException();
    }

    public function supportsClass(string $class): bool
    {
        return User::class === $class || is_subclass_of($class, User::class);
    }

    public function loadUserByIdentifier(string $identifier, array $attributes = []): UserInterface
    {
        if (!array_key_exists('https://slack.com/user_id', $attributes)) {
            throw new UserNotFoundException();
        }

        /** @var string $userId */
        $userId = $attributes['https://slack.com/user_id'];

        if (!array_key_exists('https://slack.com/team_id', $attributes)) {
            throw new UserNotFoundException();
        }

        /** @var string $workspaceId */
        $workspaceId = $attributes['https://slack.com/team_id'];

        $user = $this->userRepository->findOneBySlackIdAndWorkspaceSlackId($userId, $workspaceId);

        if ($user instanceof User) {
            return $user;
        }

        $workspace = $this->workspaceRepository->findOneBy([
            'slackId' => $workspaceId,
        ]);

        if (null === $workspace) {
            throw new AccessDeniedHttpException();
        }

        /** @var string|null $name */
        $name = $attributes['name'] ?? null;

        $user = $this->userResolver->resolve($userId, $workspace, $name);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }
}

<?php

declare(strict_types=1);

namespace App\Resolver;

use App\Entity\NotificationSettings;
use App\Entity\User;
use App\Entity\Workspace;
use App\Repository\UserRepositoryInterface;

readonly class UserResolver
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
    ) {
    }

    public function resolve(string $userId, Workspace $workspace, ?string $name = null): User
    {
        $user = $this->userRepository->findOneBy([
            'slackId' => $userId,
            'workspace' => $workspace,
        ]) ?? new User()
            ->setSlackId($userId)
            ->setWorkspace($workspace)
            ->setNotificationSettings(new NotificationSettings());

        if (null !== $name && null === $user->getName()) {
            $user->setName($name);
        }

        return $user;
    }
}

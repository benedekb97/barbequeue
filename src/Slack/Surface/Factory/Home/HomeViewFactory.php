<?php

declare(strict_types=1);

namespace App\Slack\Surface\Factory\Home;

use App\Repository\WorkspaceRepositoryInterface;
use App\Slack\Surface\Component\HomeSurface;
use App\Slack\Surface\Factory\Exception\WorkspaceNotFoundException;

readonly class HomeViewFactory
{
    public function __construct(
        private WorkspaceRepositoryInterface $workspaceRepository,
        private UserWelcomeHomeViewFactory $userWelcomeHomeViewFactory,
        private AdministratorWelcomeHomeViewFactory $administratorWelcomeHomeViewFactory,
        private UserHomeViewFactory $userHomeViewFactory,
        private AdministratorHomeViewFactory $administratorHomeViewFactory,
    ) {
    }

    /** @throws WorkspaceNotFoundException */
    public function create(
        string $userId,
        string $teamId,
        bool $firstTime,
    ): HomeSurface {
        $workspace = $this->workspaceRepository->findOneBy([
            'slackId' => $teamId,
        ]);

        if (null === $workspace) {
            throw new WorkspaceNotFoundException($teamId);
        }

        return match (true) {
            $workspace->hasAdministratorWithUserId($userId) && $firstTime => $this->administratorWelcomeHomeViewFactory->create($userId, $workspace),

            $workspace->hasAdministratorWithUserId($userId) => $this->administratorHomeViewFactory->create($userId, $workspace),

            $firstTime => $this->userWelcomeHomeViewFactory->create($userId, $workspace),

            default => $this->userHomeViewFactory->create($userId, $workspace),
        };
    }
}

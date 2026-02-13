<?php

declare(strict_types=1);

namespace App\Service\OAuth\Resolver;

use App\Entity\Workspace;
use App\Repository\WorkspaceRepositoryInterface;
use App\Service\OAuth\OAuthAccessResponse;

readonly class WorkspaceResolver
{
    public function __construct(
        private WorkspaceRepositoryInterface $repository,
    ) {
    }

    public function resolve(OAuthAccessResponse $response): Workspace
    {
        $workspace = $this->repository->findOneBy(['slackId' => $response->getTeamId()]) ?? new Workspace();

        return $workspace
            ->setSlackId($response->getTeamId())
            ->setName($response->getTeamName())
            ->setBotToken($response->getAccessToken());
    }
}

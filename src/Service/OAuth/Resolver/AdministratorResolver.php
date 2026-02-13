<?php

declare(strict_types=1);

namespace App\Service\OAuth\Resolver;

use App\Entity\Administrator;
use App\Entity\Workspace;
use App\Repository\AdministratorRepositoryInterface;
use App\Resolver\UserResolver;
use App\Service\OAuth\OAuthAccessResponse;

readonly class AdministratorResolver
{
    public function __construct(
        private AdministratorRepositoryInterface $repository,
        private UserResolver $userResolver,
    ) {
    }

    public function resolve(OAuthAccessResponse $response, Workspace $workspace): Administrator
    {
        $administrator = $this->repository->findOneByUserIdAndWorkspace(
            $response->getUserId(),
            $workspace,
        ) ?? new Administrator();

        $user = $this->userResolver->resolve($response->getUserId(), $workspace);

        return $administrator
            ->setWorkspace($workspace)
            ->setUser($user)
            ->setAddedBy(null);
    }
}

<?php

declare(strict_types=1);

namespace App\Controller\Auth;

use App\Service\OAuth\OAuthService;
use App\Service\OAuth\Resolver\AdministratorResolver;
use App\Service\OAuth\Resolver\WorkspaceResolver;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/auth', name: 'auth_')]
readonly class SecurityController
{
    private const string SLACK_APP_URL = 'https://app.slack.com/client';

    public function __construct(
        private OAuthService $oAuthService,
        private WorkspaceResolver $workspaceResolver,
        private AdministratorResolver $administratorResolver,
        private EntityManagerInterface $entityManager,
    ) {
    }

    #[Route(path: '/redirect', name: 'redirect', methods: [Request::METHOD_GET])]
    public function redirect(): RedirectResponse
    {
        return new RedirectResponse($this->oAuthService->getRedirectUrl());
    }

    #[Route(path: '/callback', name: 'callback', methods: [Request::METHOD_GET])]
    public function callback(Request $request): RedirectResponse
    {
        $response = $this->oAuthService->authorise($request->query->getString('code'));

        $workspace = $this->workspaceResolver->resolve($response);

        $this->entityManager->persist($workspace);
        $this->entityManager->flush();

        $administrator = $this->administratorResolver->resolve($response, $workspace);

        $workspace->addAdministrator($administrator);

        $this->entityManager->persist($administrator);
        $this->entityManager->flush();

        return new RedirectResponse(self::SLACK_APP_URL.'/'.$response->getTeamId().'/'.$response->getBotChannelId());
    }
}

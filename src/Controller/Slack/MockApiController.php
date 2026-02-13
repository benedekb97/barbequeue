<?php

declare(strict_types=1);

namespace App\Controller\Slack;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

readonly class MockApiController
{
    public function __construct(
        private LoggerInterface $logger,
    ) {
    }

    #[Route('/slack/mock', methods: [Request::METHOD_POST], env: 'dev')]
    public function __invoke(Request $request): Response
    {
        $this->logger->debug('Mock API call');
        $this->logger->debug($request->getContent());

        return new Response();
    }
}

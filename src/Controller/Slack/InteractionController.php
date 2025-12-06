<?php

declare(strict_types=1);

namespace App\Controller\Slack;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class InteractionController
{
    public function __construct(
        private LoggerInterface $logger,
    ) {}

    #[Route('/slack/interaction', methods: [Request::METHOD_POST])]
    public function __invoke(Request $request): Response
    {
        $this->logger->debug(json_encode($request->request->all()));

        return new JsonResponse();
    }
}

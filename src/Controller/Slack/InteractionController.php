<?php

declare(strict_types=1);

namespace App\Controller\Slack;

use App\Slack\Interaction\Component\SlackInteractionFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class InteractionController
{
    public function __construct(
        private SlackInteractionFactory $interactionFactory,
    ) {
    }

    #[Route('/slack/interaction', methods: [Request::METHOD_POST])]
    public function __invoke(Request $request): Response
    {
        $interaction = $this->interactionFactory->create($request);

        return new JsonResponse();
    }
}

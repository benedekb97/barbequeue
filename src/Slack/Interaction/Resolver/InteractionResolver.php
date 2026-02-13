<?php

declare(strict_types=1);

namespace App\Slack\Interaction\Resolver;

use App\Slack\Interaction\Exception\UnhandledInteractionTypeException;
use App\Slack\Interaction\Interaction;
use App\Slack\Interaction\InteractionType;
use Symfony\Component\HttpFoundation\Request;

readonly class InteractionResolver
{
    /** @throws \ValueError|UnhandledInteractionTypeException */
    public function resolve(InteractionType $type, Request $request): Interaction
    {
        $resolver = match ($type) {
            InteractionType::BLOCK_ACTIONS, InteractionType::MESSAGE_ACTIONS => function (Request $request): Interaction {
                /** @var array{action_id: string} $action */
                $action = $request->request->all('actions')[0];

                return Interaction::fromActionId($action['action_id']);
            },
            InteractionType::VIEW_CLOSED, InteractionType::VIEW_SUBMISSION => function (Request $request): Interaction {
                /** @var array{private_metadata: string} $view */
                $view = $request->request->all('view');

                /** @var array{action: string} $metadata */
                $metadata = json_decode($view['private_metadata'], true);

                return Interaction::from($metadata['action']);
            },
            default => fn (): Interaction => throw new UnhandledInteractionTypeException($type),
        };

        return $resolver($request);
    }
}

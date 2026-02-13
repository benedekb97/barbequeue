<?php

declare(strict_types=1);

namespace App\Slack\Interaction\Resolver;

use App\Slack\Interaction\InteractionType;
use Symfony\Component\HttpFoundation\Request;

readonly class InteractionTypeResolver
{
    /** @throws \ValueError */
    public function resolve(Request $request): InteractionType
    {
        $type = (string) $request->request->get('type');

        return InteractionType::from($type);
    }
}

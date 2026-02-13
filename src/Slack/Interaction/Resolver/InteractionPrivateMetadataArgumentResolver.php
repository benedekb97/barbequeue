<?php

declare(strict_types=1);

namespace App\Slack\Interaction\Resolver;

use Symfony\Component\HttpFoundation\Request;

readonly class InteractionPrivateMetadataArgumentResolver
{
    public function resolve(Request $request, string $argumentKey): string|int|null
    {
        /** @var array{private_metadata: string} $view */
        $view = $request->request->all('view');

        /** @var (string|int|null)[] $metadata */
        $metadata = json_decode($view['private_metadata'], true);

        if (array_key_exists($argumentKey, $metadata)) {
            return $metadata[$argumentKey];
        }

        return null;
    }
}

<?php

declare(strict_types=1);

namespace App\Slack\Interaction\Resolver;

use Symfony\Component\HttpFoundation\Request;

class InteractionPrivateMetadataResponseUrlResolver
{
    public function resolve(Request $request): ?string
    {
        /** @var array{private_metadata: string} $view */
        $view = $request->request->all('view');

        $metadata = json_decode($view['private_metadata'], true);

        if (!is_array($metadata)) {
            return null;
        }

        if (!array_key_exists('response_url', $metadata)) {
            return null;
        }

        if (!is_string($responseUrl = $metadata['response_url'])) {
            return null;
        }

        return $responseUrl;
    }
}

<?php

declare(strict_types=1);

namespace App\Slack\Interaction\Resolver;

use App\Slack\Interaction\Exception\InvalidPayloadException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;

readonly class InteractionPayloadResolver
{
    public function __construct(
        private LoggerInterface $logger,
    ) {
    }

    /** @throws InvalidPayloadException */
    public function resolve(Request $request): Request
    {
        $payload = json_decode((string) $request->request->get('payload'), true);

        if (!is_array($payload)) {
            throw new InvalidPayloadException();
        }

        $this->logger->debug('Received interaction with payload', $payload);

        return new Request(request: $payload);
    }
}

<?php

declare(strict_types=1);

namespace App\Slack\Client\Factory;

use JoliCode\Slack\Api\Client;
use JoliCode\Slack\ClientFactory as SlackClientFactory;

class ClientFactory
{
    private ?Client $client = null;

    public function create(string $accessToken): Client
    {
        return $this->client ??= SlackClientFactory::create($accessToken);
    }
}

<?php

declare(strict_types=1);

namespace App\Slack\Response\Interaction;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\Exception\ServerException;
use Symfony\Contracts\HttpClient\HttpClientInterface;

readonly class InteractionResponseHandler
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private LoggerInterface $logger,
    ) {
    }

    public function handle(string $url, SlackInteractionResponse $response): void
    {
        try {
            $this->httpClient->request('POST', $url, [
                'body' => json_encode($response->toArray(), JSON_UNESCAPED_SLASHES),
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
            ]);
        } catch (ServerException $e) {
            $response = $e->getResponse();

            $this->logger->debug($response->getContent(false));
            $this->logger->debug($e->getMessage());
        } catch (\Throwable $e) {
            $this->logger->debug($e->getMessage());
            $this->logger->debug($e::class);
        }
    }
}

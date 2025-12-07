<?php

declare(strict_types=1);

namespace App\Slack\Response\Common;

use JoliCode\Slack\Api\Client;
use JoliCode\Slack\Api\Model\ConversationsOpenPostResponse200;
use JoliCode\Slack\ClientFactory;
use JoliCode\Slack\Exception\SlackErrorResponse;
use Psr\Log\LoggerInterface;

readonly class PrivateMessageResponseHandler
{
    private Client $client;

    public function __construct(
        private LoggerInterface $logger,
        private string $slackAccessToken,
    ) {
        $this->client = ClientFactory::create($this->slackAccessToken);
    }

    public function handle(SlackPrivateMessageResponse $response): void
    {
        try {
            $conversation = $this->client->conversationsOpen([
                'users' => $response->getUserId()
            ]);
        } catch (SlackErrorResponse $exception) {
            $this->logger->debug($exception->getMessage());
            $this->logger->debug(json_encode($exception->getResponseMetadata()));
        } catch (\Throwable $exception) {
            $this->logger->debug($exception->getMessage());
            $this->logger->debug($exception::class);
        }

        if ($conversation instanceof ConversationsOpenPostResponse200) {
            $channel = $conversation->getChannel();

            $this->logger->debug($channel);
        }
    }
}

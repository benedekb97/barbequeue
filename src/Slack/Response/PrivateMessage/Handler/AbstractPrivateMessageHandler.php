<?php

declare(strict_types=1);

namespace App\Slack\Response\PrivateMessage\Handler;

use App\Entity\Workspace;
use App\Slack\Client\Exception\UnauthorisedClientException;
use App\Slack\Client\Factory\ClientFactory;
use JoliCode\Slack\Api\Model\ConversationsOpenPostResponse200;
use JoliCode\Slack\Exception\SlackErrorResponse;
use Psr\Log\LoggerInterface;

abstract readonly class AbstractPrivateMessageHandler implements PrivateMessageHandlerInterface
{
    public function __construct(
        protected ClientFactory $clientFactory,
        protected LoggerInterface $logger,
    ) {
    }

    /** @throws SlackErrorResponse|UnauthorisedClientException|\Throwable */
    protected function openConversation(string $userId, ?Workspace $workspace): string
    {
        $this->logger->debug('Opening conversation with: '.$userId);

        $conversation = $this->clientFactory->create(
            $workspace?->getBotToken() ?? throw new UnauthorisedClientException($workspace)
        )->conversationsOpen(['users' => $userId]);

        $conversation ??= null;

        if (!$conversation instanceof ConversationsOpenPostResponse200) {
            $this->logger->error('Conversation failed to open!');

            throw new UnauthorisedClientException($workspace);
        }

        /** @var array{id: string} $channel */
        $channel = $conversation->getChannel();

        return $channel['id'];
    }
}

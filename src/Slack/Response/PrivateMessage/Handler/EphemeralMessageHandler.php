<?php

declare(strict_types=1);

namespace App\Slack\Response\PrivateMessage\Handler;

use App\Enum\NotificationMode;
use App\Slack\Client\Exception\UnauthorisedClientException;
use App\Slack\Response\PrivateMessage\SlackPrivateMessage;
use JoliCode\Slack\Exception\SlackErrorResponse;

readonly class EphemeralMessageHandler extends AbstractPrivateMessageHandler implements PrivateMessageHandlerInterface
{
    public function supports(SlackPrivateMessage $message): bool
    {
        return NotificationMode::ONLY_WHEN_ACTIVE === $message->getUser()?->getNotificationSettings()?->getMode();
    }

    /** @throws SlackErrorResponse|UnauthorisedClientException|\Throwable */
    public function handle(SlackPrivateMessage $message): void
    {
        $userId = $message->getUser()?->getSlackId() ?? '';

        $channelId = $this->openConversation($userId, $message->getWorkspace());

        $this->logger->debug('Posting ephemeral message to user '.$userId);

        $this->clientFactory->create(
            $message->getWorkspace()?->getBotToken() ?? throw new UnauthorisedClientException($message->getWorkspace())
        )->chatPostEphemeral(array_merge([
            'channel' => $channelId,
            'user' => $userId,
        ], $message->toArray()));
    }
}

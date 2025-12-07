<?php

declare(strict_types=1);

namespace App\Slack\Interaction\Component;

use App\Slack\Common\Component\UserIdAwareInterface;
use App\Slack\Common\Component\UserTriggeredInteractionInterface;
use App\Slack\Interaction\Interaction;
use App\Slack\Interaction\InteractionType;
use App\Slack\Response\Common\SlackPrivateMessageResponse;
use App\Slack\Response\Interaction\SlackInteractionResponse;

class SlackInteraction implements UserTriggeredInteractionInterface
{
    private null|SlackInteractionResponse|SlackPrivateMessageResponse $response = null;

    public function __construct(
        private readonly InteractionType $type,
        private readonly Interaction $interaction,
        private readonly string $domain,
        private readonly string $userId,
        private readonly string $responseUrl,
        private readonly string $value,
        private readonly string $triggerId,
    ) {
    }

    public function getType(): InteractionType
    {
        return $this->type;
    }

    public function getInteraction(): Interaction
    {
        return $this->interaction;
    }

    public function getDomain(): string
    {
        return $this->domain;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function getResponseUrl(): string
    {
        return $this->responseUrl;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function setResponse(SlackInteractionResponse|SlackPrivateMessageResponse $response): void
    {
        $this->response = $response;
    }

    public function getResponse(): null|SlackInteractionResponse|SlackPrivateMessageResponse
    {
        return $this->response;
    }

    public function isPending(): bool
    {
        return !isset($this->response);
    }

    public function getTriggerId(): string
    {
        return $this->triggerId;
    }
}

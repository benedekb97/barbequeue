<?php

declare(strict_types=1);

namespace App\Slack\Interaction\Component;

use App\Slack\Interaction\Interaction;
use App\Slack\Interaction\InteractionType;
use App\Slack\Response\Interaction\SlackInteractionResponse;

class SlackInteraction
{
    private ?SlackInteractionResponse $response = null;

    public function __construct(
        private readonly InteractionType $type,
        private readonly Interaction $interaction,
        private readonly string $domain,
        private readonly string $userId,
        private readonly string $responseUrl,
        private readonly string $value,
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

    public function setResponse(SlackInteractionResponse $response): void
    {
        $this->response = $response;
    }

    public function getResponse(): ?SlackInteractionResponse
    {
        return $this->response;
    }

    public function isPending(): bool
    {
        return !isset($this->response);
    }
}

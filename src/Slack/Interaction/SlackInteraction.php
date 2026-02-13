<?php

declare(strict_types=1);

namespace App\Slack\Interaction;

use App\Entity\Administrator;
use App\Slack\Common\Component\UserTriggeredInteractionInterface;
use App\Slack\Response\Interaction\SlackInteractionResponse;
use App\Slack\Response\PrivateMessage\NoResponse;

class SlackInteraction implements UserTriggeredInteractionInterface
{
    private bool $pending = true;

    private SlackInteractionResponse|NoResponse|null $response = null;

    private ?Administrator $administrator = null;

    public function __construct(
        private readonly InteractionType $type,
        private readonly Interaction $interaction,
        private readonly string $teamId,
        private readonly string $userId,
        private readonly string $userName,
        private readonly string $responseUrl,
        private readonly string $value,
        private readonly string $triggerId,
        private readonly ?string $viewId = null,
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

    public function getTeamId(): string
    {
        return $this->teamId;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function getUserName(): string
    {
        return $this->userName;
    }

    public function getResponseUrl(): string
    {
        return $this->responseUrl;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function setResponse(SlackInteractionResponse|NoResponse $response): void
    {
        $this->response = $response;
    }

    public function getResponse(): SlackInteractionResponse|NoResponse|null
    {
        return $this->response;
    }

    public function isPending(): bool
    {
        return $this->pending;
    }

    public function setHandled(): void
    {
        $this->pending = false;
    }

    public function getTriggerId(): string
    {
        return $this->triggerId;
    }

    public function getAdministrator(): ?Administrator
    {
        return $this->administrator;
    }

    public function setAdministrator(?Administrator $administrator): void
    {
        $this->administrator = $administrator;
    }

    public function getViewId(): ?string
    {
        return $this->viewId;
    }
}

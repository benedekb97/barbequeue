<?php

declare(strict_types=1);

namespace App\Service\OAuth;

readonly class OAuthAccessResponse
{
    public function __construct(
        private string $teamName,
        private string $teamId,
        private string $userId,
        private string $accessToken,
        private string $botChannelId,
    ) {
    }

    public function getTeamName(): string
    {
        return $this->teamName;
    }

    public function getTeamId(): string
    {
        return $this->teamId;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function getAccessToken(): string
    {
        return $this->accessToken;
    }

    public function getBotChannelId(): string
    {
        return $this->botChannelId;
    }
}

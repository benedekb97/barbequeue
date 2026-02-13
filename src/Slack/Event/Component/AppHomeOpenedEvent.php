<?php

declare(strict_types=1);

namespace App\Slack\Event\Component;

use App\Slack\Event\Event;

readonly class AppHomeOpenedEvent implements SlackEventInterface
{
    public function __construct(
        private string $userId,
        private string $teamId,
        private string $channel,
        private string $tab,
        private bool $firstTime,
    ) {
    }

    public function getType(): Event
    {
        return Event::APP_HOME_OPENED;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function getTeamId(): string
    {
        return $this->teamId;
    }

    public function getChannel(): string
    {
        return $this->channel;
    }

    public function getTab(): string
    {
        return $this->tab;
    }

    public function isFirstTime(): bool
    {
        return $this->firstTime;
    }
}

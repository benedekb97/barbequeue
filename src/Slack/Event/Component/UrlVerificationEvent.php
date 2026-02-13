<?php

declare(strict_types=1);

namespace App\Slack\Event\Component;

use App\Slack\Event\Event;

readonly class UrlVerificationEvent implements SlackEventInterface
{
    public function __construct(
        private string $token,
        private string $challenge,
    ) {
    }

    public function getType(): Event
    {
        return Event::URL_VERIFICATION;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function getChallenge(): string
    {
        return $this->challenge;
    }
}

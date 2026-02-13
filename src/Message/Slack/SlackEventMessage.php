<?php

declare(strict_types=1);

namespace App\Message\Slack;

use App\Slack\Event\Component\SlackEventInterface;

readonly class SlackEventMessage
{
    public function __construct(
        private SlackEventInterface $event,
    ) {
    }

    public function getEvent(): SlackEventInterface
    {
        return $this->event;
    }
}

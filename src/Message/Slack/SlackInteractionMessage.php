<?php

declare(strict_types=1);

namespace App\Message\Slack;

use App\Slack\Interaction\SlackInteraction;

readonly class SlackInteractionMessage
{
    public function __construct(
        private SlackInteraction $interaction,
    ) {
    }

    public function getInteraction(): SlackInteraction
    {
        return $this->interaction;
    }
}

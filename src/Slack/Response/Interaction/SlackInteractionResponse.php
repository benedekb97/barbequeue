<?php

declare(strict_types=1);

namespace App\Slack\Response\Interaction;

use App\Slack\Message\Component\SlackMessage;

readonly class SlackInteractionResponse extends SlackMessage
{
    public function __construct(
        array $blocks,
    ) {
        parent::__construct(text: null, blocks: $blocks);
    }
}

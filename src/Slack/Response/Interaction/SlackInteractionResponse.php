<?php

declare(strict_types=1);

namespace App\Slack\Response\Interaction;

use App\Slack\Message\Component\SlackMessage;

readonly class SlackInteractionResponse extends SlackMessage
{
    public function __construct(
        array $blocks,
        private bool $replaceOriginal = false,
    ) {
        parent::__construct(text: null, blocks: $blocks);
    }

    public function toArray(): array
    {
        return array_merge(
            parent::toArray(),
            array_filter([
                'replace_original' => $this->replaceOriginal,
            ]),
        );
    }
}

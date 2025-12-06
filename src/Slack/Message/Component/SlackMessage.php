<?php

declare(strict_types=1);

namespace App\Slack\Message\Component;

use App\Slack\Block\Component\SlackBlock;

readonly class SlackMessage
{
    /** @param null|array|SlackBlock[] $blocks */
    public function __construct(
        private string $text,
        /** @var null|array|SlackBlock[] $blocks */
        private ?array $blocks,
    ) {}

    public function toArray(): array
    {
        return array_filter([
            'text' => $this->text,
            'blocks' => $this->blocks ? array_map(fn (SlackBlock $block) => $block->toArray(), $this->blocks) : null,
        ]);
    }
}

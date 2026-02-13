<?php

declare(strict_types=1);

namespace App\Slack\Message\Component;

use App\Slack\Block\Component\SlackBlock;

readonly class SlackMessage
{
    /** @param array|SlackBlock[]|null $blocks */
    public function __construct(
        protected ?string $text,
        /** @var array|(SlackBlock|null)[]|null $blocks */
        protected ?array $blocks,
    ) {
    }

    public function toArray(): array
    {
        return array_filter([
            'text' => $this->text,
            'blocks' => null !== $this->blocks ? array_map(
                fn (?SlackBlock $block) => $block?->toArray(),
                array_filter($this->blocks)
            ) : null,
        ]);
    }
}

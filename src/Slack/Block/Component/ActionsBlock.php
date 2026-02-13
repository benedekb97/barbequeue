<?php

declare(strict_types=1);

namespace App\Slack\Block\Component;

use App\Slack\Block\Block;
use App\Slack\BlockElement\Component\SlackBlockElement;

class ActionsBlock extends SlackBlock
{
    /** @param array|SlackBlockElement[] $elements */
    public function __construct(
        /** @var array|SlackBlockElement[] $elements */
        private readonly array $elements,
        private readonly ?string $blockId = null,
    ) {
    }

    public function getType(): Block
    {
        return Block::ACTIONS;
    }

    public function toArray(): array
    {
        return array_filter([
            'type' => $this->getType()->value,
            'elements' => array_map(fn (SlackBlockElement $element) => $element->toArray(), $this->elements),
            'block_id' => $this->blockId,
        ]);
    }
}

<?php

declare(strict_types=1);

namespace App\Slack\Block\Component;

use App\Slack\Block\Block;
use App\Slack\BlockElement\Component\SlackBlockElement;

class InputBlock extends SlackBlock
{
    public function __construct(
        private readonly string $label,
        private readonly SlackBlockElement $element,
        private readonly bool $dispatchAction = false,
        private readonly ?string $blockId = null,
        private readonly ?string $hint = null,
        private readonly bool $optional = false,
    ) {
    }

    public function getType(): Block
    {
        return Block::INPUT;
    }

    public function toArray(): array
    {
        return array_filter([
            'type' => $this->getType()->value,
            'label' => [
                'type' => 'plain_text',
                'text' => $this->label,
            ],
            'element' => $this->element->toArray(),
            'dispatch_action' => $this->dispatchAction,
            'block_id' => $this->blockId,
            'hint' => $this->hint ? [
                'type' => 'plain_text',
                'text' => $this->hint,
            ] : null,
            'optional' => $this->optional,
        ]);
    }
}

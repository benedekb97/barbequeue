<?php

declare(strict_types=1);

namespace App\Slack\Block\Component;

use App\Slack\Block\Block;
use App\Slack\BlockElement\Component\SlackBlockElement;

class SectionBlock extends SlackBlock
{
    public function __construct(
        private readonly string $text,
        private readonly ?string $blockId = null,
        private readonly ?SlackBlockElement $accessory = null,
        private readonly bool $expand = false,
    ) {
    }

    public function getType(): Block
    {
        return Block::SECTION;
    }

    public function toArray(): array
    {
        return array_filter([
            'type' => $this->getType()->value,
            'text' => [
                'type' => 'mrkdwn',
                'text' => $this->text,
            ],
            'block_id' => $this->blockId,
            'accessory' => $this->accessory?->toArray(),
            'expand' => $this->expand,
        ]);
    }
}

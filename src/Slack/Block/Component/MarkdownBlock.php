<?php

declare(strict_types=1);

namespace App\Slack\Block\Component;

use App\Slack\Block\Block;

class MarkdownBlock extends SlackBlock
{
    public function __construct(
        private readonly string $text,
    ) {
    }

    public function getType(): Block
    {
        return Block::MARKDOWN;
    }

    public function toArray(): array
    {
        return [
            'type' => $this->getType()->value,
            'text' => $this->text,
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Slack\Block\Component;

use App\Slack\Block\Block;

class DividerBlock extends SlackBlock
{
    public function getType(): Block
    {
        return Block::DIVIDER;
    }

    public function toArray(): array
    {
        return [
            'type' => $this->getType()->value,
        ];
    }
}

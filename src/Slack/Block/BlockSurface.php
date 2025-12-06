<?php

declare(strict_types=1);

namespace App\Slack\Block;

enum BlockSurface: string
{
    case MODAL = 'modal';
    case MESSAGE = 'message';
    case HOME_TAB = 'home-tab';

    public function getApplicableBlocks(): array
    {
        return array_filter(Block::cases(), fn (Block $block) => $block->isApplicableOnSurface($this));
    }
}

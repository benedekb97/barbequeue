<?php

declare(strict_types=1);

namespace App\Slack\Surface;

use App\Slack\Block\Block;

enum Surface: string
{
    case MODAL = 'modal';
    case MESSAGE = 'message';
    case HOME_TAB = 'home-tab';

    public function getApplicableBlocks(): array
    {
        return array_filter(Block::cases(), fn (Block $block) => $block->isApplicableOnSurface($this));
    }
}

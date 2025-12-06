<?php

declare(strict_types=1);

namespace App\Slack\BlockElement;

use App\Slack\Block\Block;
use App\Slack\Block\BlockSurface;

enum BlockElement: string
{
    case BUTTON = 'button';
    case EMAIL_INPUT = 'email_text_input';
    case NUMBER_INPUT = 'number_input';
    case PLAIN_TEXT_INPUT = 'plain_text_input';

    public function isApplicable(Block $block, BlockSurface $surface): bool
    {
        return match ($this) {
            self::BUTTON => match ($block) {
                Block::SECTION, Block::ACTIONS => true,
                default => false,
            },
            self::EMAIL_INPUT,
            self::NUMBER_INPUT => $block === Block::INPUT && $surface === BlockSurface::MODAL,
            self::PLAIN_TEXT_INPUT => $block === Block::INPUT,
        };
    }
}

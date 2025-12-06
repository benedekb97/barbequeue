<?php

declare(strict_types=1);

namespace App\Slack\Block;

enum Block: string
{
    case ACTIONS = 'actions';
    case DIVIDER = 'divider';
    case HEADER = 'header';
    case INPUT = 'input';
    case MARKDOWN = 'markdown';
    case SECTION = 'section';
    case TABLE = 'table';

    public function isApplicableOnSurface(BlockSurface $surface): bool
    {
        return match ($this) {
            self::ACTIONS, self::DIVIDER, self::HEADER, self::INPUT, self::SECTION, => true,
            self::MARKDOWN, self::TABLE, => $surface === BlockSurface::MESSAGE,
        };
    }
}

<?php

declare(strict_types=1);

namespace App\Slack\Block;

use App\Slack\Surface\Surface;

enum Block: string
{
    case ACTIONS = 'actions';
    case DIVIDER = 'divider';
    case HEADER = 'header';
    case INPUT = 'input';
    case MARKDOWN = 'markdown';
    case SECTION = 'section';
    case TABLE = 'table';

    public function isApplicableOnSurface(Surface $surface): bool
    {
        return match ($this) {
            self::ACTIONS, self::DIVIDER, self::HEADER, self::INPUT, self::SECTION, => true,
            self::MARKDOWN, self::TABLE, => $surface === Surface::MESSAGE,
        };
    }
}

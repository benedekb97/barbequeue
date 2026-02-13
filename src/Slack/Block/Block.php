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
}

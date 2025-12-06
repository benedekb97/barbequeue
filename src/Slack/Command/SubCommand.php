<?php

declare(strict_types=1);

namespace App\Slack\Command;

enum SubCommand: string
{
    // bbq sub-commands
    case JOIN = 'join';
    case LEAVE = 'leave';
    case LIST = 'list';

    // bbq-admin sub-commands
    case ADD = 'add';
    case REMOVE = 'remove';
}

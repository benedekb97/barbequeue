<?php

declare(strict_types=1);

namespace App\Slack\Surface;

enum Surface: string
{
    case MODAL = 'modal';
    case MESSAGE = 'message';
    case HOME = 'home';
}

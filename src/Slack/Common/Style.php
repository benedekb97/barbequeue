<?php

declare(strict_types=1);

namespace App\Slack\Common;

enum Style: string
{
    case PRIMARY = 'primary';
    case DANGER = 'danger';
}

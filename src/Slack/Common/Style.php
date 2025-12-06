<?php

declare(strict_types=1);

namespace App\Slack\Common;

enum Style: string
{
    case PRIMARY = 'primary';
    case SECONDARY = 'secondary';
    case DANGER = 'danger';
    case WARNING = 'warning';
}

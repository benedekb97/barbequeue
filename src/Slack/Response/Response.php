<?php

declare(strict_types=1);

namespace App\Slack\Response;

enum Response: string
{
    case IN_CHANNEL = 'in_channel';
    case EPHEMERAL = 'ephemeral';
}

<?php

declare(strict_types=1);

namespace App\Slack\Interaction;

enum InteractionArgumentLocation: string
{
    case PRIVATE_METADATA = 'private-metadata';
    case STATE = 'state';
}

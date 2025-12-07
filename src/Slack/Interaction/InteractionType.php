<?php

declare(strict_types=1);

namespace App\Slack\Interaction;

enum InteractionType: string
{
    case BLOCK_ACTIONS = 'block_actions';
    case SHORTCUT = 'shortcut';
    case MESSAGE_ACTIONS = 'message_actions';
    case VIEW_SUBMISSION = 'view_submission';
    case VIEW_CLOSED = 'view_closed';
}

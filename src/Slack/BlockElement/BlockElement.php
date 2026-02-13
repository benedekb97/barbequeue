<?php

declare(strict_types=1);

namespace App\Slack\BlockElement;

enum BlockElement: string
{
    case BUTTON = 'button';
    case EMAIL_INPUT = 'email_text_input';
    case URL_INPUT = 'url_text_input';
    case NUMBER_INPUT = 'number_input';
    case PLAIN_TEXT_INPUT = 'plain_text_input';
    case MULTI_STATIC_SELECT = 'multi_static_select';
    case MULTI_USERS_SELECT = 'multi_users_select';
    case STATIC_SELECT = 'static_select';
    case CHECKBOXES = 'checkboxes';
}

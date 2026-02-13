<?php

declare(strict_types=1);

namespace App\Slack\Event;

enum Event: string
{
    case URL_VERIFICATION = 'url_verification';
    case APP_HOME_OPENED = 'app_home_opened';
}

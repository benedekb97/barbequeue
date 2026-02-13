<?php

declare(strict_types=1);

namespace App\Slack\Event\Component;

use App\Slack\Event\Event;

interface SlackEventInterface
{
    public function getType(): Event;
}

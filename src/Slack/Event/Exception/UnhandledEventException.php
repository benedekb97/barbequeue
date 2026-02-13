<?php

declare(strict_types=1);

namespace App\Slack\Event\Exception;

use App\Slack\Event\Event;

class UnhandledEventException extends \Exception
{
    public function __construct(
        private readonly Event $event,
    ) {
        parent::__construct();
    }

    public function getEvent(): Event
    {
        return $this->event;
    }
}

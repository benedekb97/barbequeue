<?php

declare(strict_types=1);

namespace App\Slack\Response\PrivateMessage;

use App\Slack\Message\Component\SlackMessage;

readonly class NoResponse extends SlackMessage
{
    public function __construct()
    {
        parent::__construct(null, null);
    }
}

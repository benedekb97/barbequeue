<?php

declare(strict_types=1);

namespace App\Slack\Response\Common;

use App\Slack\Message\Component\SlackMessage;

readonly class SlackPrivateMessageResponse extends SlackMessage
{
    public function __construct(
        private string $userId,
        ?string $text,
        ?array $blocks
    )
    {
        parent::__construct($text, $blocks);
    }

    public function getUserId(): string
    {
        return $this->userId;
    }
}

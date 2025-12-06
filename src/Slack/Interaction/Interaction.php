<?php

declare(strict_types=1);

namespace App\Slack\Interaction;

enum Interaction: string
{
    private const string REGEX_PATTERN = '/([A-Za-z\-]+)-[0-9]+/';

    case JOIN_QUEUE = 'join-queue';
    case LEAVE_QUEUE = 'leave-queue';

    public static function fromActionId(string $actionId): self
    {
        $matches = [];

        preg_match(self::REGEX_PATTERN, $actionId, $matches);

        return self::from($matches[1] ?? '');
    }
}

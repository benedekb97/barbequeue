<?php

declare(strict_types=1);

namespace App\Slack\Response\Command;

use App\Slack\Message\Component\SlackMessage;
use App\Slack\Response\Response;

readonly class SlackCommandResponse extends SlackMessage
{
    public function __construct(
        private Response $responseType,
        ?string $text,
        ?array $blocks = null,
    ) {
        parent::__construct($text, $blocks);
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'response_type' => Response::EPHEMERAL === $this->responseType ? null : $this->responseType->value,
        ]);
    }
}

<?php

declare(strict_types=1);

namespace App\Slack\Response\Common;

use App\Slack\Block\Component\SlackBlock;
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

    public function toArray(): array
    {
        return array_filter([
            'blocks' => $this->blocks
                ? json_encode(array_map(
                    function (SlackBlock $block) {
                        return $block->toArray();
                    },
                    $this->blocks
                ), JSON_UNESCAPED_SLASHES)
                : null,
            'text' => $this->text
        ]);
    }
}

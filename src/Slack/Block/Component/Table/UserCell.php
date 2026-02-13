<?php

declare(strict_types=1);

namespace App\Slack\Block\Component\Table;

readonly class UserCell extends TableCell
{
    public function __construct(private string $userId)
    {
    }

    public function toArray(): array
    {
        return [
            'type' => 'rich_text',
            'elements' => [
                [
                    'type' => 'rich_text_section',
                    'elements' => [
                        [
                            'type' => 'user',
                            'user_id' => $this->userId,
                        ],
                    ],
                ],
            ],
        ];
    }
}

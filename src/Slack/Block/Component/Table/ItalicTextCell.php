<?php

declare(strict_types=1);

namespace App\Slack\Block\Component\Table;

readonly class ItalicTextCell extends TableCell
{
    public function __construct(
        private string $text,
    ) {
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
                            'type' => 'text',
                            'text' => $this->text,
                            'style' => [
                                'italic' => true,
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}

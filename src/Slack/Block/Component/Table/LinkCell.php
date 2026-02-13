<?php

declare(strict_types=1);

namespace App\Slack\Block\Component\Table;

readonly class LinkCell extends TableCell
{
    public function __construct(
        private string $url,
        private ?string $text = null,
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
                            'type' => 'link',
                            'url' => $this->url,
                            'text' => $this->text ?? $this->url,
                        ],
                    ],
                ],
            ],
        ];
    }
}

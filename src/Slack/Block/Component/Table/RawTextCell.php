<?php

declare(strict_types=1);

namespace App\Slack\Block\Component\Table;

readonly class RawTextCell extends TableCell
{
    public function __construct(
        private ?string $text,
    ) {
    }

    public function toArray(): array
    {
        return [
            'type' => 'raw_text',
            'text' => $this->text ?? '',
        ];
    }
}

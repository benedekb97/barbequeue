<?php

declare(strict_types=1);

namespace App\Slack\Common\Component;

use App\Slack\Common\Style;

readonly class SlackConfirmation
{
    public function __construct(
        private string $title,
        private string $text,
        private string $confirm,
        private string $deny,
        private ?Style $style = null,
    ) {
    }

    public function toArray(): array
    {
        return array_filter([
            'title' => [
                'type' => 'plain_text',
                'text' => $this->title,
            ],
            'text' => [
                'type' => 'plain_text',
                'text' => $this->text,
            ],
            'confirm' => [
                'type' => 'plain_text',
                'text' => $this->confirm,
            ],
            'deny' => [
                'type' => 'plain_text',
                'text' => $this->deny,
            ],
            'style' => $this->style?->value,
        ]);
    }
}

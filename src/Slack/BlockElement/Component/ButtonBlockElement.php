<?php

declare(strict_types=1);

namespace App\Slack\BlockElement\Component;

use App\Slack\BlockElement\BlockElement;
use App\Slack\Common\Component\SlackConfirmation;
use App\Slack\Common\Style;

class ButtonBlockElement extends SlackBlockElement
{
    public function __construct(
        private readonly string $text,
        private readonly ?string $actionId = null,
        private readonly ?string $url = null,
        private readonly ?string $value = null,
        private readonly ?Style $style = null,
        private readonly ?SlackConfirmation $confirm = null,
    ) {
    }

    public function getType(): BlockElement
    {
        return BlockElement::BUTTON;
    }

    public function toArray(): array
    {
        return array_filter([
            'type' => $this->getType()->value,
            'text' => [
                'type' => 'plain_text',
                'text' => $this->text,
            ],
            'action_id' => $this->actionId,
            'url' => $this->url,
            'value' => $this->value,
            'style' => $this->style?->value,
            'confirm' => $this->confirm?->toArray(),
        ]);
    }
}

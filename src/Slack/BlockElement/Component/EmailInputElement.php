<?php

declare(strict_types=1);

namespace App\Slack\BlockElement\Component;

use App\Slack\BlockElement\BlockElement;

class EmailInputElement extends SlackBlockElement
{
    public function __construct(
        private readonly ?string $actionId = null,
        private readonly ?string $initialValue = null,
        private readonly bool $focusOnLoad = false,
        private readonly ?string $placeholder = null,
    ) {
    }

    public function getType(): BlockElement
    {
        return BlockElement::EMAIL_INPUT;
    }

    public function toArray(): array
    {
        return array_filter([
            'type' => $this->getType()->value,
            'action_id' => $this->actionId,
            'initial_value' => $this->initialValue,
            'focus_on_load' => $this->focusOnLoad,
            'placeholder' => [
                'type' => 'plain_text',
                'text' => $this->placeholder,
                'emoji' => false,
            ],
        ], fn ($element) => null !== $element);
    }
}

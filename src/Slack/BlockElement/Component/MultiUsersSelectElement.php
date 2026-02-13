<?php

declare(strict_types=1);

namespace App\Slack\BlockElement\Component;

use App\Slack\BlockElement\BlockElement;

class MultiUsersSelectElement extends SlackBlockElement
{
    public function __construct(
        private readonly ?string $actionId = null,
        private readonly ?string $placeholder = null,
        private readonly bool $focusOnLoad = false,
    ) {
    }

    public function getType(): BlockElement
    {
        return BlockElement::MULTI_USERS_SELECT;
    }

    public function toArray(): array
    {
        return array_filter([
            'action_id' => $this->actionId,
            'type' => $this->getType()->value,
            'placeholder' => null !== $this->placeholder
                ? [
                    'text' => $this->placeholder,
                    'type' => 'plain_text',
                ]
                : null,
            'focus_on_load' => $this->focusOnLoad,
        ]);
    }
}

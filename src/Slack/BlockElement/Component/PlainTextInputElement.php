<?php

declare(strict_types=1);

namespace App\Slack\BlockElement\Component;

use App\Slack\BlockElement\BlockElement;

class PlainTextInputElement extends EmailInputElement
{
    public function __construct(
        private readonly bool $multiline = false,
        private readonly ?int $minLength = null,
        private readonly ?int $maxLength = null,
        ?string $actionId = null,
        ?string $initialValue = null,
        bool $focusOnLoad = false,
        ?string $placeholder = null,
    ) {
        parent::__construct($actionId, $initialValue, $focusOnLoad, $placeholder);
    }

    public function getType(): BlockElement
    {
        return BlockElement::PLAIN_TEXT_INPUT;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), array_filter([
            'multiline' => $this->multiline,
            'min_length' => $this->minLength ? "$this->minLength" : null,
            'max_length' => $this->maxLength ? "$this->maxLength" : null,
        ], fn ($element) => null !== $element));
    }
}

<?php

declare(strict_types=1);

namespace App\Slack\BlockElement\Component;

use App\Slack\BlockElement\BlockElement;

class NumberInputElement extends EmailInputElement
{
    public function __construct(
        private readonly bool $isDecimalAllowed,
        private readonly ?float $minValue = null,
        private readonly ?float $maxValue = null,
        ?string $actionId = null,
        ?string $initialValue = null,
        bool $focusOnLoad = false,
        ?string $placeholder = null,
    ) {
        parent::__construct($actionId, $initialValue, $focusOnLoad, $placeholder);
    }

    public function getType(): BlockElement
    {
        return BlockElement::NUMBER_INPUT;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), array_filter([
            'is_decimal_allowed' => $this->isDecimalAllowed,
            'min_value' => $this->minValue ? "$this->minValue" : null,
            'max_value' => $this->maxValue ? "$this->maxValue" : null,
        ], fn ($element) => null !== $element));
    }
}

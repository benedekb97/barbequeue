<?php

declare(strict_types=1);

namespace App\Slack\BlockElement\Component;

use App\Slack\BlockElement\BlockElement;

class CheckboxesElement extends MultiStaticSelectElement
{
    public function __construct(
        ?string $actionId = null,
        array $options = [],
        array $initialOptions = [],
        bool $focusOnLoad = false,
    ) {
        parent::__construct(
            $actionId,
            options: $options,
            initialOptions: $initialOptions,
            focusOnLoad: $focusOnLoad,
        );
    }

    public function getType(): BlockElement
    {
        return BlockElement::CHECKBOXES;
    }
}

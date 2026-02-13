<?php

declare(strict_types=1);

namespace App\Slack\BlockElement\Component;

use App\Slack\BlockElement\BlockElement;

class UrlInputElement extends EmailInputElement
{
    public function getType(): BlockElement
    {
        return BlockElement::URL_INPUT;
    }
}

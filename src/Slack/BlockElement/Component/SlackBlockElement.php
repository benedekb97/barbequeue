<?php

declare(strict_types=1);

namespace App\Slack\BlockElement\Component;

use App\Slack\BlockElement\BlockElement;

abstract class SlackBlockElement
{
    abstract public function getType(): BlockElement;

    abstract public function toArray(): array;
}

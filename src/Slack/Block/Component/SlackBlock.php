<?php

declare(strict_types=1);

namespace App\Slack\Block\Component;

use App\Slack\Block\Block;

abstract class SlackBlock
{
    abstract public function getType(): Block;

    abstract public function toArray(): array;
}

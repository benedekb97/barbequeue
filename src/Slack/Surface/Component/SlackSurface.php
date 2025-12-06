<?php

declare(strict_types=1);

namespace App\Slack\Surface\Component;

use App\Slack\Surface\Surface;

abstract class SlackSurface
{
    abstract public function getType(): Surface;

    abstract public function toArray(): array;
}

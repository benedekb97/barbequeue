<?php

declare(strict_types=1);

namespace App\Slack\Block\Component\Table;

abstract readonly class TableCell
{
    abstract public function toArray(): array;
}

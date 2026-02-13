<?php

declare(strict_types=1);

namespace App\Slack\Block\Component\Table;

readonly class TableRow
{
    /** @param TableCell[] $cells */
    public function __construct(
        /** @var TableCell[] */
        private array $cells,
    ) {
    }

    public function toArray(): array
    {
        return array_map(function (TableCell $cell) {
            return $cell->toArray();
        }, $this->cells);
    }
}

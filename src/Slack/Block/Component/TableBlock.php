<?php

declare(strict_types=1);

namespace App\Slack\Block\Component;

use App\Slack\Block\Block;
use App\Slack\Block\Component\Table\TableRow;

class TableBlock extends SlackBlock
{
    /** @param TableRow[] $rows */
    public function __construct(
        /** @var TableRow[] $rows */
        private readonly array $rows,
        private readonly ?string $blockId = null,
        private readonly ?array $columnSettings = null,
    ) {
    }

    public function getType(): Block
    {
        return Block::TABLE;
    }

    public function toArray(): array
    {
        return array_filter([
            'type' => $this->getType()->value,
            'rows' => array_map(fn (TableRow $row) => $row->toArray(), $this->rows),
            'block_id' => $this->blockId,
            'column_settings' => $this->columnSettings,
        ]);
    }
}

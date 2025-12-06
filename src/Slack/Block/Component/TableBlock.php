<?php

declare(strict_types=1);

namespace App\Slack\Block\Component;

use App\Slack\Block\Block;

class TableBlock extends SlackBlock
{
    public function __construct(
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
            'rows' => $this->rows,
            'block_id' => $this->blockId,
            'column_settings' => $this->columnSettings,
        ]);
    }
}

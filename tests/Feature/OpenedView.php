<?php

declare(strict_types=1);

namespace App\Tests\Feature;

use App\Slack\Surface\Surface;

readonly class OpenedView
{
    public function __construct(
        private Surface $type,
        private array $blocks,
        private string $title,
    ) {
    }

    public function getType(): Surface
    {
        return $this->type;
    }

    public function getBlocks(): array
    {
        return $this->blocks;
    }

    public function getTitle(): string
    {
        return $this->title;
    }
}

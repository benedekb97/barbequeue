<?php

declare(strict_types=1);

namespace App\Tests\Feature;

use App\Slack\Surface\Surface;

readonly class UpdatedView
{
    public function __construct(
        private string $viewId,
        private Surface $type,
        private array $blocks,
        private string $title,
    ) {
    }

    public function getViewId(): string
    {
        return $this->viewId;
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

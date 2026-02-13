<?php

declare(strict_types=1);

namespace App\Slack\Surface\Component;

use App\Entity\Workspace;
use App\Slack\Block\Component\SlackBlock;
use App\Slack\Surface\Surface;

class HomeSurface extends SlackSurface
{
    public function __construct(
        private readonly string $userId,
        private readonly Workspace $workspace,
        /** @var SlackBlock[] $blocks */
        private readonly array $blocks,
    ) {
    }

    public function getType(): Surface
    {
        return Surface::HOME;
    }

    public function toArray(): array
    {
        return [
            'user_id' => $this->userId,
            'view' => json_encode([
                'type' => $this->getType()->value,
                'blocks' => array_map(fn (SlackBlock $block) => $block->toArray(), $this->blocks),
            ]),
        ];
    }

    public function getWorkspace(): Workspace
    {
        return $this->workspace;
    }
}

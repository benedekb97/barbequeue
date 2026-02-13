<?php

declare(strict_types=1);

namespace App\Tests\Feature;

readonly class SentPrivateMessage
{
    public function __construct(
        private string $userId,
        private array $blocks,
    ) {
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function getBlocks(): array
    {
        return $this->blocks;
    }
}

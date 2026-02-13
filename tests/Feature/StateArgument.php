<?php

declare(strict_types=1);

namespace App\Tests\Feature;

use App\Slack\BlockElement\BlockElement;

readonly class StateArgument
{
    public function __construct(
        private BlockElement $type,
        private string $name,
        private array $value,
    ) {
    }

    public function toArray(): array
    {
        return [
            $this->name => array_merge(
                ['type' => $this->type->value],
                $this->value,
            ),
        ];
    }
}

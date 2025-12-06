<?php

declare(strict_types=1);

namespace App\Slack\Command\Component;

readonly class SlackCommandArguments
{
    private int $argumentCount;

    public function __construct(
        private array $arguments,
    ) {
        $this->argumentCount = count($this->arguments);
    }

    public function getArgumentCount(): int
    {
        return $this->argumentCount;
    }

    public function getArguments(): array
    {
        return array_values($this->arguments);
    }
}

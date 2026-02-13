<?php

declare(strict_types=1);

namespace App\Tests\Feature;

readonly class SentRequest
{
    public function __construct(
        private string $method,
        private string $url,
        private array $options,
    ) {
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getOptions(): array
    {
        return $this->options;
    }
}

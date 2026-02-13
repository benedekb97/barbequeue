<?php

declare(strict_types=1);

namespace App\Slack\Event\Exception;

class UnrecognisedEventException extends \Exception
{
    public function __construct(
        private readonly ?string $type = null,
    ) {
        parent::__construct();
    }

    public function getType(): ?string
    {
        return $this->type;
    }
}

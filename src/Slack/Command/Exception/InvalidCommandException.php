<?php

declare(strict_types=1);

namespace App\Slack\Command\Exception;

class InvalidCommandException extends \Exception
{
    public function __construct(private readonly string $commandText)
    {
        parent::__construct();
    }

    public function getCommandText(): string
    {
        return $this->commandText;
    }
}

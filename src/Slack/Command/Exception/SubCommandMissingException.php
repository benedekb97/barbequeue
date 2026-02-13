<?php

declare(strict_types=1);

namespace App\Slack\Command\Exception;

use App\Slack\Command\Command;

class SubCommandMissingException extends \Exception
{
    public function __construct(
        private readonly Command $command,
    ) {
        parent::__construct('Sub-command missing from command '.$command->value);
    }

    public function getCommand(): Command
    {
        return $this->command;
    }
}

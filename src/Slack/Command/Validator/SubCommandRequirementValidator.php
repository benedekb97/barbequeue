<?php

declare(strict_types=1);

namespace App\Slack\Command\Validator;

use App\Slack\Command\Command;
use App\Slack\Command\Exception\SubCommandMissingException;

readonly class SubCommandRequirementValidator
{
    /** @throws SubCommandMissingException */
    public function validate(Command $command, ?string $subCommand): void
    {
        if (!$command->isSubCommandRequired()) {
            return;
        }

        if (!empty($subCommand)) {
            return;
        }

        throw new SubCommandMissingException($command);
    }
}

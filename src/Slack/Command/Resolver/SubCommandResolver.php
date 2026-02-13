<?php

declare(strict_types=1);

namespace App\Slack\Command\Resolver;

use App\Slack\Command\Command;
use App\Slack\Command\Exception\InvalidSubCommandException;
use App\Slack\Command\Exception\SubCommandMissingException;
use App\Slack\Command\SubCommand;
use App\Slack\Command\Validator\SubCommandRequirementValidator;
use App\Slack\Command\Validator\SubCommandValidator;
use Symfony\Component\HttpFoundation\Request;

readonly class SubCommandResolver
{
    public function __construct(
        private SubCommandRequirementValidator $subCommandRequirementValidator,
        private SubCommandValidator $subCommandValidator,
    ) {
    }

    /** @throws InvalidSubCommandException|SubCommandMissingException */
    public function resolve(Command $command, Request $request): ?SubCommand
    {
        if (!$command->hasSubCommands()) {
            return null;
        }

        $this->subCommandRequirementValidator->validate($command, $subCommandText = $this->getSubCommandString($request));

        return $this->resolveFromString($command, $subCommandText);
    }

    /** @throws InvalidSubCommandException */
    public function resolveFromString(Command $command, ?string $subCommandText): ?SubCommand
    {
        if (empty($subCommandText)) {
            return null;
        }

        $subCommand = SubCommand::tryFromAlias($subCommandText);

        $this->subCommandValidator->validate($command, $subCommand, $subCommandText);

        return $subCommand;
    }

    private function getSubCommandString(Request $request): string
    {
        $requestText = (string) $request->request->get('text');

        $commandParts = explode(' ', $requestText);

        return $commandParts[0];
    }
}

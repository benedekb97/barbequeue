<?php

declare(strict_types=1);

namespace App\Slack\Command\Resolver;

use App\Slack\Command\Command;
use App\Slack\Command\Exception\InvalidCommandException;
use Symfony\Component\HttpFoundation\Request;

class CommandResolver
{
    /** @throws InvalidCommandException */
    public function resolve(Request $request): Command
    {
        $command = Command::tryFrom($commandString = $this->getCommandString($request));

        if ($command instanceof Command) {
            return $command;
        }

        throw new InvalidCommandException($commandString);
    }

    private function getCommandString(Request $request): string
    {
        return trim((string) $request->request->get('command'), '/');
    }
}

<?php

declare(strict_types=1);

namespace App\Slack\Command\Component;

use App\Slack\Command\Command;
use App\Slack\Command\Component\Exception\InvalidArgumentCountException;
use App\Slack\Command\Component\Exception\InvalidSubCommandException;
use App\Slack\Command\Component\Exception\SubCommandMissingException;
use App\Slack\Command\SubCommand;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\Request;

class SlackCommandFactory
{
    /**
     * @throws SubCommandMissingException
     * @throws InvalidSubCommandException
     * @throws InvalidArgumentCountException
     * @throws \ValueError
     */
    public function createFromRequest(Request $request): SlackCommand
    {
        $command = Command::from($this->getCommandString($request));

        $subCommand = $this->getSubCommand($command, $request);

        if ($subCommand !== null && !in_array($subCommand, $command->getSubCommands())) {
            throw new InvalidSubCommandException($command, $subCommand);
        }

        $arguments = $this->getArguments($request, $command, $subCommand);

        if (count($arguments) < $command->getRequiredArgumentCount($subCommand)) {
            throw new InvalidArgumentCountException($command, $subCommand);
        }

        return new SlackCommand(
            $command,
            $arguments,
            $this->getDomain($request),
            $this->getUserId($request),
            $this->getResponseUrl($request),
            $this->getTriggerId($request),
            $subCommand
        );
    }

    private function getSubCommand(Command $command, Request $request): ?SubCommand
    {
        if (!$command->hasSubCommands()) {
            return null;
        }

        if ($command->isSubCommandRequired() && empty($this->getSubCommandString($request))) {
            throw new SubCommandMissingException($command);
        }

        if (empty($subCommandString = $this->getSubCommandString($request))) {
            return null;
        }

        if ($command->isSubCommandRequired()) {
            return SubCommand::from($subCommandString);
        }

        return SubCommand::tryFrom($subCommandString);
    }

    private function getCommandString(Request $request): string
    {
        return trim((string) $request->request->get('command'), '/');
    }

    private function getSubCommandString(Request $request): string
    {
        $requestText = (string) $request->request->get('text');

        $commandParts = explode(' ', $requestText);

        return $commandParts[0];
    }

    /**
     * @return array|string[]
     *
     * @throws SubCommandMissingException
     */
    private function getArguments(Request $request, Command $command, ?SubCommand $subCommand): array
    {
        $commandParts = new ArrayCollection(explode(' ', (string) $request->request->get('text')));

        $argumentValues = array_values($commandParts->slice($subCommand === null ? 0 : 1));

        $argumentKeys = $command->getArguments($subCommand);

        $arguments = [];

        foreach ($argumentKeys as $key => $argumentKey) {
            $arguments[$argumentKey] = $argumentValues[$key] ?? null;
        }

        return array_filter($arguments);
    }

    private function getDomain(Request $request): string
    {
        return (string) $request->request->get('team_domain');
    }

    private function getUserId(Request $request): string
    {
        return (string) $request->request->get('user_id');
    }

    private function getTriggerId(Request $request): string
    {
        return (string) $request->request->get('trigger_id');
    }

    private function getResponseUrl(Request $request): string
    {
        return (string) $request->request->get('response_url');
    }
}

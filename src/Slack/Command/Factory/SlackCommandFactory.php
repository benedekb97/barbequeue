<?php

declare(strict_types=1);

namespace App\Slack\Command\Factory;

use App\Slack\Command\Exception\InvalidArgumentCountException;
use App\Slack\Command\Exception\InvalidCommandException;
use App\Slack\Command\Exception\InvalidSubCommandException;
use App\Slack\Command\Exception\SubCommandMissingException;
use App\Slack\Command\Resolver\ArgumentsResolver;
use App\Slack\Command\Resolver\CommandResolver;
use App\Slack\Command\Resolver\SubCommandResolver;
use App\Slack\Command\SlackCommand;
use Symfony\Component\HttpFoundation\Request;

readonly class SlackCommandFactory
{
    public function __construct(
        private CommandResolver $commandResolver,
        private SubCommandResolver $subCommandResolver,
        private ArgumentsResolver $argumentsResolver,
    ) {
    }

    /**
     * @throws InvalidCommandException
     * @throws SubCommandMissingException
     * @throws InvalidSubCommandException
     * @throws InvalidArgumentCountException
     */
    public function createFromRequest(Request $request): SlackCommand
    {
        $command = $this->commandResolver->resolve($request);

        $subCommand = $this->subCommandResolver->resolve($command, $request);

        $arguments = $this->argumentsResolver->resolve($command, $subCommand, $request);

        return new SlackCommand(
            $command,
            $arguments,
            $this->getTeamId($request),
            $this->getUserId($request),
            $this->getUserName($request),
            $this->getResponseUrl($request),
            $this->getTriggerId($request),
            $subCommand,
        );
    }

    private function getTeamId(Request $request): string
    {
        return (string) $request->request->get('team_id');
    }

    private function getUserId(Request $request): string
    {
        return (string) $request->request->get('user_id');
    }

    private function getUserName(Request $request): string
    {
        return (string) $request->request->get('user_name');
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

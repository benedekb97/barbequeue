<?php

declare(strict_types=1);

namespace App\Slack\Command;

use App\Entity\Administrator;
use App\Slack\Common\Component\UserTriggeredInteractionInterface;
use App\Slack\Response\Command\SlackCommandResponse;
use App\Slack\Response\Interaction\SlackInteractionResponse;
use App\Slack\Response\PrivateMessage\NoResponse;
use http\Exception\InvalidArgumentException;

class SlackCommand implements UserTriggeredInteractionInterface
{
    private SlackCommandResponse|SlackInteractionResponse|NoResponse|null $response = null;

    private ?Administrator $administrator = null;

    public function __construct(
        private readonly Command $command,
        /** @var (string|int)[] $arguments */
        private readonly array $arguments,
        private readonly string $teamId,
        private readonly string $userId,
        private readonly string $userName,
        private readonly string $responseUrl,
        private readonly string $triggerId,
        private readonly ?SubCommand $subCommand = null,
    ) {
    }

    public function getCommand(): Command
    {
        return $this->command;
    }

    public function getSubCommand(): ?SubCommand
    {
        return $this->subCommand;
    }

    public function getArguments(): array
    {
        return $this->arguments;
    }

    public function getArgumentString(CommandArgument $argument): string
    {
        if (!$this->command->hasArgument($this->subCommand, $argument)) {
            throw new InvalidArgumentException(sprintf('Argument %s is not a valid argument for command %s.', $argument->value, $this->command->value));
        }

        return (string) $this->arguments[$argument->value];
    }

    public function getOptionalArgumentString(CommandArgument $argument): ?string
    {
        if (!array_key_exists($argument->value, $this->arguments)) {
            return null;
        }

        return (string) $this->arguments[$argument->value];
    }

    public function getOptionalArgumentInteger(CommandArgument $argument): ?int
    {
        if (!array_key_exists($argument->value, $this->arguments)) {
            return null;
        }

        return (int) $this->arguments[$argument->value];
    }

    public function isPending(): bool
    {
        return !isset($this->response);
    }

    public function getResponse(): SlackCommandResponse|SlackInteractionResponse|NoResponse|null
    {
        return $this->response;
    }

    public function setResponse(SlackCommandResponse|SlackInteractionResponse|NoResponse|null $response): void
    {
        $this->response = $response;
    }

    public function getTeamId(): string
    {
        return $this->teamId;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function getUserName(): string
    {
        return $this->userName;
    }

    public function getResponseUrl(): string
    {
        return $this->responseUrl;
    }

    public function getTriggerId(): string
    {
        return $this->triggerId;
    }

    public function setAdministrator(?Administrator $administrator): void
    {
        $this->administrator = $administrator;
    }

    public function getAdministrator(): ?Administrator
    {
        return $this->administrator;
    }
}

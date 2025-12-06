<?php

declare(strict_types=1);

namespace App\Slack\Command\Component;

use App\Slack\Command\Command;
use App\Slack\Command\SubCommand;
use App\Slack\Response\Command\SlackCommandResponse;
use App\Slack\Response\Interaction\SlackInteractionResponse;

class SlackCommand
{
    private null|SlackCommandResponse|SlackInteractionResponse $response = null;

    public function __construct(
        private readonly Command $command,
        /** @var array|string[] $arguments */
        private readonly array $arguments,
        private readonly string $domain,
        private readonly string $userId,
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

    public function getArgument(string $key): string
    {
        return $this->arguments[$key] ?? throw new \InvalidArgumentException(sprintf('Argument %s does not exist on command %s. Available arguments: %s', $key, $this->command->value, implode(', ', $this->command->getArguments($this->subCommand))));
    }

    public function isPending(): bool
    {
        return !isset($this->response);
    }

    public function getResponse(): null|SlackCommandResponse|SlackInteractionResponse
    {
        return $this->response;
    }

    public function setResponse(null|SlackCommandResponse|SlackInteractionResponse $response): void
    {
        $this->response = $response;
    }

    public function getDomain(): string
    {
        return $this->domain;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function getResponseUrl(): string
    {
        return $this->responseUrl;
    }

    public function getTriggerId(): string
    {
        return $this->triggerId;
    }
}

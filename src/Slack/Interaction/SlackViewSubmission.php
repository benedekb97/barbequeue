<?php

declare(strict_types=1);

namespace App\Slack\Interaction;

use App\Slack\Common\Component\UserTriggeredInteractionInterface;

class SlackViewSubmission extends SlackInteraction implements UserTriggeredInteractionInterface
{
    /** @param (int|string|null)[] $arguments */
    public function __construct(
        Interaction $interaction,
        string $teamId,
        string $userId,
        string $userName,
        /** @var (int|string|int[]|string[]|null)[] $arguments */
        private readonly array $arguments,
        string $triggerId,
        ?string $responseUrl,
    ) {
        parent::__construct(
            InteractionType::VIEW_SUBMISSION,
            $interaction,
            $teamId,
            $userId,
            $userName,
            $responseUrl ?? '',
            '',
            $triggerId,
        );
    }

    /** @return (int|string|int[]|string[]|null)[] */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    public function isArgumentProvided(string $argument): bool
    {
        return array_key_exists($argument, $this->arguments);
    }

    /** @return int|string|int[]|string[]|null */
    public function getArgument(string $argument): string|int|array|null
    {
        return $this->arguments[$argument] ?? null;
    }

    public function getArgumentInteger(string $argument): ?int
    {
        $argument = $this->getArgument($argument);

        if (null === $argument) {
            return null;
        }

        if (is_array($argument)) {
            return null;
        }

        return (int) $argument;
    }

    public function getArgumentString(string $argument): ?string
    {
        $argument = $this->getArgument($argument);

        if (null === $argument) {
            return null;
        }

        if (is_array($argument)) {
            return null;
        }

        return (string) $argument;
    }

    /** @return int[]|null */
    public function getArgumentIntArray(string $argument): ?array
    {
        $argument = $this->getArgument($argument);

        if (null === $argument) {
            return null;
        }

        if (!is_array($argument)) {
            return null;
        }

        $values = [];

        foreach ($argument as $value) {
            $values[] = (int) $value;
        }

        return $values;
    }

    /** @return string[]|null */
    public function getArgumentStringArray(string $argument): ?array
    {
        $argument = $this->getArgument($argument);

        if (null === $argument) {
            return null;
        }

        if (!is_array($argument)) {
            return null;
        }

        $values = [];

        foreach ($argument as $value) {
            $values[] = (string) $value;
        }

        return $values;
    }
}

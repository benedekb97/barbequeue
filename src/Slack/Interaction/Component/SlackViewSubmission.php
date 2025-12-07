<?php

declare(strict_types=1);

namespace App\Slack\Interaction\Component;

use App\Slack\Common\Component\UserTriggeredInteractionInterface;
use App\Slack\Interaction\Interaction;
use App\Slack\Interaction\InteractionType;

class SlackViewSubmission extends SlackInteraction implements UserTriggeredInteractionInterface
{
    private bool $pending = true;

    public function __construct(
        readonly Interaction $interaction,
        readonly string $domain,
        readonly string $userId,
        private readonly array $arguments,
        string $triggerId,
    )
    {
        parent::__construct(
            InteractionType::VIEW_SUBMISSION,
            $interaction,
            $domain,
            $userId,
            '',
            '',
            $triggerId
        );
    }

    public function getArguments(): array
    {
        return $this->arguments;
    }

    public function isArgumentProvided(string $argument): bool
    {
        return array_key_exists($argument, $this->arguments);
    }

    public function getArgument(string $argument): null|string|int
    {
        return $this->arguments[$argument] ?? null;
    }

    public function isPending(): bool
    {
        return $this->pending;
    }

    public function setHandled(): void
    {
        $this->pending = false;
    }
}

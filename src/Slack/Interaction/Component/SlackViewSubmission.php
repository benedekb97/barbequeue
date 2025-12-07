<?php

declare(strict_types=1);

namespace App\Slack\Interaction\Component;

use App\Slack\Interaction\Interaction;
use App\Slack\Interaction\InteractionType;

class SlackViewSubmission extends SlackInteraction
{
    public function __construct(
        readonly Interaction $interaction,
        readonly string $domain,
        readonly string $userId,
        private readonly array $arguments,
    )
    {
        parent::__construct(
            InteractionType::VIEW_SUBMISSION,
            $interaction,
            $domain,
            $userId,
            '',
            ''
        );
    }

    public function getArguments(): array
    {
        return $this->arguments;
    }

    public function getArgument(string $argument): ?string
    {
        return $this->arguments[$argument] ?? null;
    }
}

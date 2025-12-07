<?php

declare(strict_types=1);

namespace App\Slack\Interaction;

use App\Slack\Interaction\Handler\EditQueueInteractionHandler;

enum Interaction: string
{
    private const string REGEX_PATTERN = '/([A-Za-z\-]+)-[0-9]+/';

    case JOIN_QUEUE = 'join-queue';
    case LEAVE_QUEUE = 'leave-queue';
    case EDIT_QUEUE = 'edit-queue';
    case EDIT_QUEUE_ACTION = 'edit-queue-action';

    public static function fromActionId(string $actionId): self
    {
        $matches = [];

        preg_match(self::REGEX_PATTERN, $actionId, $matches);

        return self::from($matches[1] ?? '');
    }

    /** @return string[] */
    public function getRequiredArguments(): array
    {
        return match ($this) {
            self::EDIT_QUEUE => array_keys(EditQueueInteractionHandler::REQUIRED_ARGUMENTS),
            default => [],
        };
    }

    /** @return string[] */
    public function getOptionalArguments(): array
    {
        return match ($this) {
            self::EDIT_QUEUE => array_keys(EditQueueInteractionHandler::OPTIONAL_ARGUMENTS),
            default => [],
        };
    }

    /** @return string[] */
    public function getArguments(): array
    {
        return array_merge($this->getRequiredArguments(), $this->getOptionalArguments());
    }

    public function getArgumentLocation(string $argument): InteractionArgumentLocation
    {
        return match ($this) {
            self::EDIT_QUEUE => EditQueueInteractionHandler::ARGUMENT_LOCATION_MAP[$argument],
            default => InteractionArgumentLocation::STATE,
        };
    }
}

<?php

declare(strict_types=1);

namespace App\Slack\Interaction\Handler\Queue\Pop;

use App\Service\Queue\Pop\PopQueueContext;
use App\Slack\Interaction\Handler\SlackInteractionHandlerInterface;
use App\Slack\Interaction\Interaction;
use App\Slack\Interaction\InteractionType;
use App\Slack\Interaction\SlackInteraction;

readonly class PopQueueInteractionHandler extends AbstractPopQueueInteractionHandler implements SlackInteractionHandlerInterface
{
    public function getContext(SlackInteraction $interaction): PopQueueContext
    {
        return new PopQueueContext(
            $interaction->getValue(),
            $interaction->getTeamId(),
            $interaction->getUserId(),
        );
    }

    public function supports(SlackInteraction $interaction): bool
    {
        return Interaction::POP_QUEUE_ACTION === $interaction->getInteraction()
            && InteractionType::BLOCK_ACTIONS === $interaction->getType();
    }
}

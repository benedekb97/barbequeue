<?php

declare(strict_types=1);

namespace App\Slack\Interaction\Handler\Queue\Pop;

use App\Service\Queue\Pop\PopQueueContext;
use App\Slack\Interaction\Handler\SlackInteractionHandlerInterface;
use App\Slack\Interaction\Interaction;
use App\Slack\Interaction\InteractionType;
use App\Slack\Interaction\SlackInteraction;
use App\Slack\Interaction\SlackViewSubmission;
use App\Slack\Surface\Component\ModalArgument;

readonly class PopQueueViewSubmissionHandler extends AbstractPopQueueInteractionHandler implements SlackInteractionHandlerInterface
{
    public function getContext(SlackInteraction $interaction): PopQueueContext
    {
        if (!$interaction instanceof SlackViewSubmission) {
            throw new \BadMethodCallException();
        }

        return new PopQueueContext(
            (string) $interaction->getArgumentString(ModalArgument::QUEUE->value),
            $interaction->getTeamId(),
            $interaction->getUserId(),
            $interaction->getArgumentInteger(ModalArgument::QUEUED_USER_ID->value),
        );
    }

    public function supports(SlackInteraction $interaction): bool
    {
        return Interaction::POP_QUEUE_ACTION === $interaction->getInteraction()
            && InteractionType::VIEW_SUBMISSION === $interaction->getType();
    }
}

<?php

declare(strict_types=1);

namespace App\Slack\Interaction\Handler\Queue\Leave;

use App\Service\Queue\Leave\LeaveQueueContext;
use App\Slack\Interaction\Handler\SlackInteractionHandlerInterface;
use App\Slack\Interaction\Interaction;
use App\Slack\Interaction\InteractionType;
use App\Slack\Interaction\SlackInteraction;
use App\Slack\Interaction\SlackViewSubmission;
use App\Slack\Surface\Component\ModalArgument;

readonly class LeaveQueueViewSubmissionHandler extends AbstractLeaveQueueInteractionHandler implements SlackInteractionHandlerInterface
{
    public function supports(SlackInteraction $interaction): bool
    {
        return Interaction::LEAVE_QUEUE === $interaction->getInteraction()
            && InteractionType::VIEW_SUBMISSION === $interaction->getType();
    }

    public function getContext(SlackInteraction $interaction): LeaveQueueContext
    {
        if (!$interaction instanceof SlackViewSubmission) {
            throw new \BadMethodCallException();
        }

        return new LeaveQueueContext(
            (string) $interaction->getArgumentString(ModalArgument::QUEUE->value),
            $interaction->getTeamId(),
            $interaction->getUserId(),
            $interaction->getArgumentInteger(ModalArgument::QUEUED_USER_ID->value),
        );
    }
}

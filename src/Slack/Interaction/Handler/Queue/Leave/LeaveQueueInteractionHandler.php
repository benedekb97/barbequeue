<?php

declare(strict_types=1);

namespace App\Slack\Interaction\Handler\Queue\Leave;

use App\Service\Queue\Leave\LeaveQueueContext;
use App\Slack\Interaction\Handler\SlackInteractionHandlerInterface;
use App\Slack\Interaction\Interaction;
use App\Slack\Interaction\InteractionType;
use App\Slack\Interaction\SlackInteraction;

readonly class LeaveQueueInteractionHandler extends AbstractLeaveQueueInteractionHandler implements SlackInteractionHandlerInterface
{
    public function supports(SlackInteraction $interaction): bool
    {
        return Interaction::LEAVE_QUEUE === $interaction->getInteraction()
            && InteractionType::BLOCK_ACTIONS === $interaction->getType();
    }

    public function getContext(SlackInteraction $interaction): LeaveQueueContext
    {
        return new LeaveQueueContext(
            $interaction->getValue(),
            $interaction->getTeamId(),
            $interaction->getUserId(),
        );
    }
}

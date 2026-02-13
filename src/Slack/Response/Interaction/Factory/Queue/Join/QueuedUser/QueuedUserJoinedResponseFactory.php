<?php

declare(strict_types=1);

namespace App\Slack\Response\Interaction\Factory\Queue\Join\QueuedUser;

use App\Entity\Queue;
use App\Entity\QueuedUser;
use App\Slack\Block\Component\SectionBlock;
use App\Slack\Response\Interaction\SlackInteractionResponse;

readonly class QueuedUserJoinedResponseFactory
{
    public function create(QueuedUser $queuedUser): SlackInteractionResponse
    {
        return new SlackInteractionResponse([
            new SectionBlock($this->getMessage($queuedUser)),
        ]);
    }

    private function getMessage(QueuedUser $queuedUser): string
    {
        /** @var Queue $queue */
        $queue = $queuedUser->getQueue();

        return sprintf(
            'You are %s in the `%s` queue.',
            $queue->getPlacementString($queuedUser->getUser()?->getSlackId() ?? ''),
            $queue->getName()
        );
    }
}

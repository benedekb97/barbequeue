<?php

declare(strict_types=1);

namespace App\Slack\Response\Common\Factory;

use App\Entity\Queue;
use App\Entity\QueuedUser;
use App\Slack\Block\Component\DividerBlock;
use App\Slack\Block\Component\HeaderBlock;
use App\Slack\Block\Component\SectionBlock;
use App\Slack\Response\Common\SlackPrivateMessageResponse;
use App\Slack\Response\Interaction\Factory\Traits\WithPlacements;

class RemovedFromQueueMessageFactory
{
    use WithPlacements;

    public function create(QueuedUser $queuedUser, Queue $queue): SlackPrivateMessageResponse
    {
        if (!$queue->canLeave($queuedUser->getUserId())) {
            return new SlackPrivateMessageResponse(
                $queuedUser->getUserId(),
                text: null,
                blocks: [
                    new SectionBlock('You have left the '.$queue->getName().' queue.'),
                ]
            );
        }

        return new SlackPrivateMessageResponse(
            $queuedUser->getUserId(),
            text: null,
            blocks: [
                new HeaderBlock('You have been removed from your last place in the '.$queue->getName().' queue.'),
                new DividerBlock(),
                new SectionBlock(
                    sprintf(
                        'You are now %s in the %s queue.',
                        $this->getPlacementString($queue, $queuedUser->getUserId()),
                        $queue->getName()
                    )
                ),
            ]
        );
    }
}

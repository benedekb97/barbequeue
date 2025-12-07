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

    public function create(QueuedUser $queuedUser, Queue $queue, bool $automatic): SlackPrivateMessageResponse
    {
        if (!$queue->canLeave($queuedUser->getUserId())) {
            return new SlackPrivateMessageResponse(
                $queuedUser->getUserId(),
                text: null,
                blocks: [
                    new SectionBlock(
                        $automatic
                            ? 'Your time at the front of the '.$queue->getName().' queue is up.'
                            : 'You have been removed from the front of the '.$queue->getName().' queue.'
                    ),
                ]
            );
        }

        $headerMessage = $automatic
            ? 'Your time at the front of the '.$queue->getName().' queue is up.'
            : 'You have been removed from the front of the '.$queue->getName().' queue.';

        return new SlackPrivateMessageResponse(
            $queuedUser->getUserId(),
            text: null,
            blocks: [
                new HeaderBlock($headerMessage),
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

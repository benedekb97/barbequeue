<?php

declare(strict_types=1);

namespace App\Slack\Response\PrivateMessage\Factory;

use App\Entity\Queue;
use App\Entity\QueuedUser;
use App\Slack\Block\Component\SectionBlock;
use App\Slack\Response\Interaction\Factory\Traits\WithPlacements;
use App\Slack\Response\PrivateMessage\SlackPrivateMessage;

class RemovedFromQueueMessageFactory
{
    use WithPlacements;

    public function create(QueuedUser $queuedUser, Queue $queue, bool $automatic): SlackPrivateMessage
    {
        if (!$queue->canLeave($queuedUser->getUser()?->getSlackId() ?? '')) {
            return new SlackPrivateMessage(
                $queuedUser->getUser(),
                $queue->getWorkspace(),
                text: null,
                blocks: [
                    new SectionBlock(
                        $automatic
                            ? 'Your time at the front of the `'.$queue->getName().'` queue is up.'
                            : 'You have been removed from the front of the `'.$queue->getName().'` queue.'
                    ),
                ]
            );
        }

        $headerMessage = $automatic
            ? 'Your time at the front of the `'.$queue->getName().'` queue is up.'
            : 'You have been removed from the front of the `'.$queue->getName().'` queue.';

        return new SlackPrivateMessage(
            $queuedUser->getUser(),
            $queue->getWorkspace(),
            text: null,
            blocks: [
                new SectionBlock($headerMessage),
                new SectionBlock(
                    sprintf(
                        'You are now %s in the `%s` queue.',
                        $this->getPlacementString($queue, $queuedUser->getUser()?->getSlackId() ?? ''),
                        $queue->getName()
                    )
                ),
            ]
        );
    }
}

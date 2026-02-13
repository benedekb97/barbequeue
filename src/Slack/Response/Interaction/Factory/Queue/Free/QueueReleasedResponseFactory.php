<?php

declare(strict_types=1);

namespace App\Slack\Response\Interaction\Factory\Queue\Free;

use App\Entity\Queue;
use App\Slack\Block\Component\SectionBlock;
use App\Slack\Response\Interaction\Factory\Traits\WithPlacements;
use App\Slack\Response\Interaction\SlackInteractionResponse;

readonly class QueueReleasedResponseFactory
{
    use WithPlacements;

    public function create(Queue $queue, string $userId): SlackInteractionResponse
    {
        if (!$queue->canLeave($userId)) {
            return new SlackInteractionResponse([
                new SectionBlock(sprintf('You have left the front of the `%s` queue.', $queue->getName())),
            ]);
        }

        return new SlackInteractionResponse([
            new SectionBlock(sprintf(
                'You have left the front of the `%s` queue.',
                $queueName = $queue->getName()
            )),
            new SectionBlock(sprintf(
                'You are now %s in the `%s` queue.',
                $this->getPlacementString($queue, $userId),
                $queueName,
            )),
        ]);
    }
}

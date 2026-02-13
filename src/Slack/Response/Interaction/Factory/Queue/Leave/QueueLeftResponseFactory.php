<?php

declare(strict_types=1);

namespace App\Slack\Response\Interaction\Factory\Queue\Leave;

use App\Entity\Queue;
use App\Slack\Block\Component\SectionBlock;
use App\Slack\Response\Interaction\Factory\Traits\WithPlacements;
use App\Slack\Response\Interaction\SlackInteractionResponse;

readonly class QueueLeftResponseFactory
{
    use WithPlacements;

    public function create(Queue $queue, string $userId): SlackInteractionResponse
    {
        if (!$queue->canLeave($userId)) {
            return new SlackInteractionResponse([
                new SectionBlock('You have left the `'.$queue->getName().'` queue.'),
            ]);
        }

        return new SlackInteractionResponse([
            new SectionBlock('You have been removed from the `'.$queue->getName().'` queue.'),
            new SectionBlock(
                sprintf(
                    'You are now %s in the `%s` queue.',
                    $this->getPlacementString($queue, $userId),
                    $queue->getName()
                )
            ),
        ]);
    }
}

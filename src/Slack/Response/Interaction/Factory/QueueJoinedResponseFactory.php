<?php

declare(strict_types=1);

namespace App\Slack\Response\Interaction\Factory;

use App\Entity\Queue;
use App\Slack\Block\Component\DividerBlock;
use App\Slack\Block\Component\HeaderBlock;
use App\Slack\Block\Component\SectionBlock;
use App\Slack\Response\Interaction\Factory\Traits\WithPlacements;
use App\Slack\Response\Interaction\SlackInteractionResponse;

class QueueJoinedResponseFactory
{
    use WithPlacements;

    public function create(Queue $queue, string $userId): SlackInteractionResponse
    {
        return new SlackInteractionResponse([
            new HeaderBlock('You have been added to the '.$queue->getName().' queue.'),
            new DividerBlock(),
            new SectionBlock(
                sprintf(
                    'You are the %s in the %s queue.',
                    $this->getPlacementString($queue, $userId),
                    $queue->getName()
                )
            ),
        ]);
    }
}

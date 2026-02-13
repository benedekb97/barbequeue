<?php

declare(strict_types=1);

namespace App\Slack\Response\Interaction\Factory\Queue\Join;

use App\Entity\Queue;
use App\Slack\Block\Component\SectionBlock;
use App\Slack\Response\Interaction\SlackInteractionResponse;

readonly class UnableToJoinQueueResponseFactory
{
    public function create(Queue $queue): SlackInteractionResponse
    {
        return new SlackInteractionResponse([
            new SectionBlock($this->getMessage($queue)),
        ]);
    }

    private function getMessage(Queue $queue): string
    {
        return 1 === $queue->getMaximumEntriesPerUser()
            ? sprintf('You are already in the `%s` queue.', $queue->getName())
            : sprintf(
                'You can only join the `%s` queue *%d* times.',
                $queue->getName(),
                $queue->getMaximumEntriesPerUser()
            );
    }
}

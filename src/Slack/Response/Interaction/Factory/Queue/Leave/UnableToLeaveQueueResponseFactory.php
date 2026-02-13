<?php

declare(strict_types=1);

namespace App\Slack\Response\Interaction\Factory\Queue\Leave;

use App\Entity\Queue;
use App\Slack\Block\Component\SectionBlock;
use App\Slack\Response\Interaction\SlackInteractionResponse;

readonly class UnableToLeaveQueueResponseFactory
{
    public function create(Queue $queue): SlackInteractionResponse
    {
        return new SlackInteractionResponse([
            new SectionBlock(
                sprintf('You are not in the `%s` queue.', $queue->getName())
            ),
        ]);
    }
}

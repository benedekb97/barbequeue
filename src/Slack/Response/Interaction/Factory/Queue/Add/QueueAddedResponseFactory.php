<?php

declare(strict_types=1);

namespace App\Slack\Response\Interaction\Factory\Queue\Add;

use App\Entity\Queue;
use App\Slack\Block\Component\SectionBlock;
use App\Slack\Response\Interaction\SlackInteractionResponse;

readonly class QueueAddedResponseFactory
{
    public function create(Queue $queue): SlackInteractionResponse
    {
        return new SlackInteractionResponse(
            [
                new SectionBlock(sprintf('A queue called `%s` has been created!', $queue->getName())),
            ]
        );
    }
}

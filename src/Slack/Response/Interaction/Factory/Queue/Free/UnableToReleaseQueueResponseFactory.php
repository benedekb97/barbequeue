<?php

declare(strict_types=1);

namespace App\Slack\Response\Interaction\Factory\Queue\Free;

use App\Entity\Queue;
use App\Slack\Block\Component\SectionBlock;
use App\Slack\Response\Interaction\SlackInteractionResponse;

readonly class UnableToReleaseQueueResponseFactory
{
    public function create(Queue $queue): SlackInteractionResponse
    {
        return new SlackInteractionResponse([
            new SectionBlock(sprintf('You are not at the front of the `%s` queue.', $queue->getName())),
        ]);
    }
}

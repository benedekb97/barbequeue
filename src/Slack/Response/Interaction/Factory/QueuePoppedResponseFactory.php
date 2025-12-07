<?php

declare(strict_types=1);

namespace App\Slack\Response\Interaction\Factory;

use App\Entity\Queue;
use App\Slack\Block\Component\SectionBlock;
use App\Slack\Response\Interaction\SlackInteractionResponse;

class QueuePoppedResponseFactory
{
    public function create(Queue $queue): SlackInteractionResponse
    {
        return new SlackInteractionResponse([
            new SectionBlock('Queue '.$queue->getName().' has been popped.'),
        ]);
    }
}

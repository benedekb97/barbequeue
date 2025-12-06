<?php

declare(strict_types=1);

namespace App\Slack\Response\Interaction\Factory;

use App\Entity\Queue;
use App\Slack\Block\Component\SectionBlock;
use App\Slack\Response\Interaction\SlackInteractionResponse;

class QueueJoinedResponseFactory
{
    public function create(Queue $queue): SlackInteractionResponse
    {
        return new SlackInteractionResponse([
            new SectionBlock('You are now in the '.$queue->getName().' queue.')
        ]);
    }
}

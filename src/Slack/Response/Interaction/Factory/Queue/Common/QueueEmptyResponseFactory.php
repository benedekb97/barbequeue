<?php

declare(strict_types=1);

namespace App\Slack\Response\Interaction\Factory\Queue\Common;

use App\Entity\Queue;
use App\Slack\Block\Component\SectionBlock;
use App\Slack\Response\Interaction\SlackInteractionResponse;

readonly class QueueEmptyResponseFactory
{
    public function create(Queue $queue): SlackInteractionResponse
    {
        return new SlackInteractionResponse([
            new SectionBlock('The `'.$queue->getName().'` queue is empty!'),
        ]);
    }
}

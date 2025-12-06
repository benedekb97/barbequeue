<?php

declare(strict_types=1);

namespace App\Slack\Response\Interaction\Factory;

use App\Entity\Queue;
use App\Slack\Block\Component\DividerBlock;
use App\Slack\Block\Component\HeaderBlock;
use App\Slack\Block\Component\SectionBlock;
use App\Slack\Response\Interaction\SlackInteractionResponse;

class UnableToLeaveQueueResponseFactory
{
    public function create(Queue $queue): SlackInteractionResponse
    {
        return new SlackInteractionResponse([
            new HeaderBlock('Unable to leave the '.$queue->getName().' queue.'),
            new DividerBlock(),
            new SectionBlock(
                 sprintf('You are not in the %s queue.', $queue->getName())
            )
        ]);
    }
}

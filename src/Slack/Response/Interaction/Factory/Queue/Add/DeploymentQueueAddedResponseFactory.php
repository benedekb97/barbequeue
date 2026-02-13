<?php

declare(strict_types=1);

namespace App\Slack\Response\Interaction\Factory\Queue\Add;

use App\Entity\DeploymentQueue;
use App\Slack\Block\Component\SectionBlock;
use App\Slack\Response\Interaction\SlackInteractionResponse;

readonly class DeploymentQueueAddedResponseFactory
{
    public function create(DeploymentQueue $queue): SlackInteractionResponse
    {
        return new SlackInteractionResponse(
            [
                new SectionBlock(sprintf('A deployment queue called `%s` has been created!', $queue->getName())),
            ]
        );
    }
}

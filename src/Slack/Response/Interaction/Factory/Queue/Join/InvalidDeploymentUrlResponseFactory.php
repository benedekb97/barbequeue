<?php

declare(strict_types=1);

namespace App\Slack\Response\Interaction\Factory\Queue\Join;

use App\Entity\Queue;
use App\Slack\Block\Component\SectionBlock;
use App\Slack\BlockElement\Component\ButtonBlockElement;
use App\Slack\Response\Interaction\SlackInteractionResponse;

readonly class InvalidDeploymentUrlResponseFactory
{
    public function create(string $deploymentLink, Queue $queue): SlackInteractionResponse
    {
        $queueName = $queue->getName();

        return new SlackInteractionResponse([
            new SectionBlock(
                sprintf('The deployment link you entered was not a valid URL: `%s`. Please try again.', $deploymentLink),
                accessory: new ButtonBlockElement(
                    'Join '.$queueName.' queue',
                    'join-queue-'.$queue->getId(),
                    value: $queueName,
                ),
            ),
        ]);
    }
}

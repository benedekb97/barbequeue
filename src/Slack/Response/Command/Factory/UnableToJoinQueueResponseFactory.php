<?php

declare(strict_types=1);

namespace App\Slack\Response\Command\Factory;

use App\Entity\Queue;
use App\Slack\Block\Component\DividerBlock;
use App\Slack\Block\Component\HeaderBlock;
use App\Slack\Block\Component\SectionBlock;
use App\Slack\Response\Command\SlackCommandResponse;
use App\Slack\Response\Response;

class UnableToJoinQueueResponseFactory
{
    public function create(Queue $queue): SlackCommandResponse
    {
        return new SlackCommandResponse(
            Response::EPHEMERAL,
            $header = 'Unable to join the '.$queue->getName().' queue.',
            [
                new HeaderBlock($header),
                new DividerBlock(),
                new SectionBlock(
                    $queue->getMaximumEntriesPerUser() === 1
                        ? sprintf('You are already in the %s queue.', $queue->getName())
                        : sprintf(
                            'You can only join the %s queue %d times.',
                            $queue->getName(),
                            $queue->getMaximumEntriesPerUser()
                    ),
                )
            ]
        );
    }
}

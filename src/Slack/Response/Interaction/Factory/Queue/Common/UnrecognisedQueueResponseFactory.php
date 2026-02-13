<?php

declare(strict_types=1);

namespace App\Slack\Response\Interaction\Factory\Queue\Common;

use App\Slack\Block\Component\SectionBlock;
use App\Slack\Response\Interaction\Factory\Queue\Block\UnrecognisedQueueActionsBlockFactory;
use App\Slack\Response\Interaction\SlackInteractionResponse;

readonly class UnrecognisedQueueResponseFactory
{
    public function __construct(
        private UnrecognisedQueueActionsBlockFactory $actionsBlockFactory,
    ) {
    }

    public function create(
        string $queueName,
        string $teamId,
        ?string $userId = null,
        bool $withActions = true,
    ): SlackInteractionResponse {
        $actionsBlock = $withActions ? $this->actionsBlockFactory->create($teamId, $userId) : null;

        return new SlackInteractionResponse(array_filter([
            new SectionBlock(
                sprintf(
                    "We couldn't find a queue called `%s`.%s",
                    $queueName,
                    null !== $actionsBlock ? ' Try these on for size:' : ''
                ),
            ),
            $actionsBlock,
        ]));
    }
}

<?php

declare(strict_types=1);

namespace App\Slack\Response\Interaction\Factory\Queue\Edit;

use App\Entity\Queue;
use App\Slack\Block\Component\SectionBlock;
use App\Slack\Response\Interaction\Factory\Queue\Block\AdministratorQueueActionsFactory;
use App\Slack\Response\Interaction\Factory\Queue\Block\QueueInformationTableFactory;
use App\Slack\Response\Interaction\SlackInteractionResponse;

readonly class QueueEditedResponseFactory
{
    public function __construct(
        private QueueInformationTableFactory $queueInformationTableFactory,
        private AdministratorQueueActionsFactory $actionsFactory,
    ) {
    }

    public function create(Queue $queue): SlackInteractionResponse
    {
        return new SlackInteractionResponse([
            new SectionBlock('Queue `'.$queue->getName().'` edited successfully.'),
            $this->queueInformationTableFactory->create($queue),
            $this->actionsFactory->create($queue),
        ]);
    }
}

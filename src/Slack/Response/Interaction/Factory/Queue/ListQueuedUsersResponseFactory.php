<?php

declare(strict_types=1);

namespace App\Slack\Response\Interaction\Factory\Queue;

use App\Entity\DeploymentQueue;
use App\Entity\Queue;
use App\Slack\Block\Component\SectionBlock;
use App\Slack\Block\Component\TableBlock;
use App\Slack\Response\Interaction\Factory\Queue\Block\QueuedUsersTable\DeploymentTableFactory;
use App\Slack\Response\Interaction\Factory\Queue\Block\QueuedUsersTable\DeploymentWithExpiryTableFactory;
use App\Slack\Response\Interaction\Factory\Queue\Block\QueuedUsersTable\QueuedUsersTableFactory;
use App\Slack\Response\Interaction\Factory\Queue\Block\QueuedUsersTable\QueuedUsersWithExpiryTableFactory;
use App\Slack\Response\Interaction\SlackInteractionResponse;

readonly class ListQueuedUsersResponseFactory
{
    public function __construct(
        private QueuedUsersTableFactory $queuedUsersTableFactory,
        private QueuedUsersWithExpiryTableFactory $queuedUsersWithExpiryTableFactory,
        private DeploymentTableFactory $deploymentTableFactory,
        private DeploymentWithExpiryTableFactory $deploymentWithExpiryTableFactory,
    ) {
    }

    public function create(Queue $queue): SlackInteractionResponse
    {
        return new SlackInteractionResponse([
            new SectionBlock('Users currently in the `'.$queue->getName().'` queue'),
            $this->getTableBlock($queue),
        ]);
    }

    private function getTableBlock(Queue $queue): TableBlock
    {
        return match (true) {
            $queue instanceof DeploymentQueue && $queue->hasQueuedUserWithExpiryMinutes() => $this->deploymentWithExpiryTableFactory->create($queue),
            $queue->hasQueuedUserWithExpiryMinutes() => $this->queuedUsersWithExpiryTableFactory->create($queue),
            $queue instanceof DeploymentQueue => $this->deploymentTableFactory->create($queue),
            default => $this->queuedUsersTableFactory->create($queue),
        };
    }
}

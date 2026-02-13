<?php

declare(strict_types=1);

namespace App\Slack\Response\Interaction\Factory\Queue\Join;

use App\Entity\Deployment;
use App\Entity\QueuedUser;
use App\Slack\Response\Interaction\Factory\Queue\Join\QueuedUser\DeploymentJoinedResponseFactory;
use App\Slack\Response\Interaction\Factory\Queue\Join\QueuedUser\QueuedUserJoinedResponseFactory;
use App\Slack\Response\Interaction\SlackInteractionResponse;
use App\Slack\Response\PrivateMessage\NoResponse;

readonly class QueueJoinedResponseFactory
{
    public function __construct(
        private DeploymentJoinedResponseFactory $deploymentResponseFactory,
        private QueuedUserJoinedResponseFactory $queuedUserResponseFactory,
    ) {
    }

    public function create(QueuedUser $queuedUser): SlackInteractionResponse|NoResponse
    {
        return match (true) {
            $queuedUser instanceof Deployment => $this->deploymentResponseFactory->create($queuedUser),
            default => $this->queuedUserResponseFactory->create($queuedUser),
        };
    }
}

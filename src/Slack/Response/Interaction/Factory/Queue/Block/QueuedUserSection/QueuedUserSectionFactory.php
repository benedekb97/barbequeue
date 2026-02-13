<?php

declare(strict_types=1);

namespace App\Slack\Response\Interaction\Factory\Queue\Block\QueuedUserSection;

use App\Entity\Deployment;
use App\Entity\QueuedUser;
use App\Slack\Block\Component\SectionBlock;

readonly class QueuedUserSectionFactory
{
    public function __construct(
        private SimpleQueuedUserSectionFactory $simpleQueuedUserSectionFactory,
        private DeploymentSectionFactory $deploymentSectionFactory,
    ) {
    }

    public function create(QueuedUser $queuedUser, int $place): SectionBlock
    {
        return match (true) {
            $queuedUser instanceof Deployment => $this->deploymentSectionFactory->create($queuedUser, $place),
            default => $this->simpleQueuedUserSectionFactory->create($queuedUser, $place),
        };
    }
}

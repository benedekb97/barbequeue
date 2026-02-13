<?php

declare(strict_types=1);

namespace App\Slack\Response\Interaction\Factory\Queue\Block;

use App\Entity\DeploymentQueue;
use App\Entity\Queue;
use App\Slack\Block\Component\SectionBlock;
use App\Slack\Response\Interaction\Factory\Queue\Block\QueuedUserSection\DeploymentSectionFactory;
use App\Slack\Response\Interaction\Factory\Queue\Block\QueuedUserSection\QueuedUserSectionFactory;

readonly class QueuedUsersSectionsFactory
{
    public function __construct(
        private QueuedUserSectionFactory $queuedUserSectionFactory,
        private DeploymentSectionFactory $deploymentSectionFactory,
    ) {
    }

    /** @return SectionBlock[] */
    public function create(Queue $queue): array
    {
        $blocks = [];

        $queueName = $queue->getName();

        if (0 === $queue->getQueuedUsers()->count()) {
            return [
                new SectionBlock(sprintf('The `%s` queue is empty.', $queueName)),
            ];
        }

        if ($queue instanceof DeploymentQueue) {
            $activeDeployments = $queue->getActiveDeployments();

            if (empty($activeDeployments)) {
                $blocks[] = new SectionBlock(sprintf('_Nobody in the `%s` queue is deploying at the moment._', $queueName));
            } else {
                $blocks[] = new SectionBlock(sprintf('Users currently deploying in the `%s` queue: ', $queueName));

                foreach ($activeDeployments as $deployment) {
                    $blocks[] = $this->deploymentSectionFactory->create($deployment);
                }
            }

            $place = 0;

            $pendingDeployments = $queue->getPendingDeployments();

            if (empty($pendingDeployments)) {
                $blocks[] = new SectionBlock('_No deployments waiting._');
            } else {
                $blocks[] = new SectionBlock(sprintf('Users waiting in the `%s` queue: ', $queueName));

                foreach ($pendingDeployments as $deployment) {
                    $blocks[] = $this->deploymentSectionFactory->create($deployment, ++$place);
                }
            }
        } else {
            $blocks[] = new SectionBlock(sprintf('Users queued in the `%s` queue:', $queueName));

            $place = 0;

            foreach ($queue->getSortedUsers() as $user) {
                $blocks[] = $this->queuedUserSectionFactory->create($user, ++$place);
            }
        }

        return $blocks;
    }
}

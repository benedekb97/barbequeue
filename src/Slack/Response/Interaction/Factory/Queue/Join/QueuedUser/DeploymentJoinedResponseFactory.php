<?php

declare(strict_types=1);

namespace App\Slack\Response\Interaction\Factory\Queue\Join\QueuedUser;

use App\Entity\Deployment;
use App\Entity\DeploymentQueue;
use App\Entity\Queue;
use App\Entity\Repository;
use App\Slack\Block\Component\DividerBlock;
use App\Slack\Block\Component\SectionBlock;
use App\Slack\Response\Interaction\Factory\Queue\Block\QueuedUsersSectionsFactory;
use App\Slack\Response\Interaction\Factory\Repository\Deployments\RepositoryDeploymentsSectionsFactory;
use App\Slack\Response\Interaction\SlackInteractionResponse;
use App\Slack\Response\PrivateMessage\NoResponse;

readonly class DeploymentJoinedResponseFactory
{
    public function __construct(
        private QueuedUsersSectionsFactory $queuedUsersSectionsFactory,
        private RepositoryDeploymentsSectionsFactory $repositoryDeploymentsSectionsFactory,
    ) {
    }

    public function create(Deployment $deployment): SlackInteractionResponse|NoResponse
    {
        if ($deployment->isActive()) {
            return new SlackInteractionResponse([
                new SectionBlock($this->getStartNowMessage($deployment)),
            ]);
        }

        /** @var DeploymentQueue $queue */
        $queue = $deployment->getQueue();

        /** @var Repository $repository */
        $repository = $deployment->getRepository();

        /** @var Deployment $blocker */
        $blocker = $deployment->getBlocker();

        if ($queue !== $blocker->getQueue()) {
            return new SlackInteractionResponse([
                $this->getPlacementSection($queue, $deployment, $blocker),
                new DividerBlock(),
                ...$this->repositoryDeploymentsSectionsFactory->create($repository),
            ]);
        }

        return new SlackInteractionResponse([
            $this->getPlacementSection($queue, $deployment, $blocker),
            new DividerBlock(),
            ...$this->queuedUsersSectionsFactory->create($queue),
        ]);
    }

    private function getStartNowMessage(Deployment $deployment): string
    {
        if (null !== $deployment->getExpiresAt()) {
            return sprintf(
                'You can start your deployment on `%s` now! You have `%d minutes` before you are removed from the front of the queue.',
                $deployment->getRepository()?->getName(),
                $deployment->getExpiresAt()->diffInMinutes(absolute: true),
            );
        }

        return sprintf('You can start your deployment on `%s` now!', $deployment->getRepository()?->getName());
    }

    private function getPlacementSection(Queue $queue, Deployment $deployment, Deployment $blocker): SectionBlock
    {
        return new SectionBlock(sprintf(
            'You are now %s in the `%s` queue. You will have to wait for %s in the `%s` queue to finish deploying `%s` to `%s` before you can begin.',
            $queue->getPlacementString($deployment->getUser()?->getSlackId() ?? ''),
            $queue->getName(),
            $blocker->getUserLink(),
            $blocker->getQueue()?->getName(),
            $blocker->getDescription(),
            $blocker->getRepository()?->getName(),
        ));
    }
}

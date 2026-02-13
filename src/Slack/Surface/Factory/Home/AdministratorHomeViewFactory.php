<?php

declare(strict_types=1);

namespace App\Slack\Surface\Factory\Home;

use App\Entity\Workspace;
use App\Slack\Block\Component\DividerBlock;
use App\Slack\Block\Component\HeaderBlock;
use App\Slack\Block\Component\SectionBlock;
use App\Slack\Block\Component\SlackBlock;
use App\Slack\Response\Interaction\Factory\Queue\Block\AdministratorQueueActionsFactory;
use App\Slack\Response\Interaction\Factory\Queue\Block\QueuedUsersSectionsFactory;
use App\Slack\Response\Interaction\Factory\Queue\Block\QueueInformationSectionFactory;
use App\Slack\Response\Interaction\Factory\Repository\Deployments\RepositoryDeploymentsSectionsFactory;
use App\Slack\Surface\Component\HomeSurface;

readonly class AdministratorHomeViewFactory
{
    public function __construct(
        private QueuedUsersSectionsFactory $queuedUsersSectionsFactory,
        private QueueInformationSectionFactory $queueInformationSectionFactory,
        private AdministratorQueueActionsFactory $administratorQueueActionsFactory,
        private RepositoryDeploymentsSectionsFactory $repositoryDeploymentsSectionsFactory,
    ) {
    }

    public function create(string $userId, Workspace $workspace): HomeSurface
    {
        return new HomeSurface(
            $userId,
            $workspace,
            [
                new SectionBlock('To view a list of available commands type `/bbq-admin help` or `/bbq help`'),
                new DividerBlock(),
                new HeaderBlock('Repositories'),
                ...$this->getRepositoryBlocks($workspace),
                new HeaderBlock('Queues'),
                ...$this->getQueueBlocks($workspace),
            ]
        );
    }

    /** @return SlackBlock[] */
    private function getRepositoryBlocks(Workspace $workspace): array
    {
        $blocks = [];

        foreach ($workspace->getRepositories() as $repository) {
            $deployments = $this->repositoryDeploymentsSectionsFactory->create($repository);

            foreach ($deployments as $deploymentSection) {
                $blocks[] = $deploymentSection;
            }

            $blocks[] = new DividerBlock();
        }

        return $blocks;
    }

    /** @return SlackBlock[] */
    private function getQueueBlocks(Workspace $workspace): array
    {
        $blocks = [];

        foreach ($workspace->getQueues() as $queue) {
            $users = $this->queuedUsersSectionsFactory->create($queue);

            foreach ($users as $userSection) {
                $blocks[] = $userSection;
            }

            $blocks[] = $this->queueInformationSectionFactory->create($queue);

            $blocks[] = $this->administratorQueueActionsFactory->create($queue);

            $blocks[] = new DividerBlock();
        }

        return $blocks;
    }
}

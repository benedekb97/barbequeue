<?php

declare(strict_types=1);

namespace App\Slack\Surface\Factory\Home;

use App\Entity\Workspace;
use App\Slack\Block\Component\DividerBlock;
use App\Slack\Block\Component\HeaderBlock;
use App\Slack\Block\Component\SectionBlock;
use App\Slack\Block\Component\SlackBlock;
use App\Slack\Response\Interaction\Factory\Queue\Block\QueuedUsersSectionsFactory;
use App\Slack\Response\Interaction\Factory\Queue\Block\QueueInformationSectionFactory;
use App\Slack\Surface\Component\HomeSurface;

readonly class UserHomeViewFactory
{
    public function __construct(
        private QueuedUsersSectionsFactory $queuedUsersSectionsFactory,
        private QueueInformationSectionFactory $queueInformationSectionFactory,
    ) {
    }

    public function create(string $userId, Workspace $workspace): HomeSurface
    {
        return new HomeSurface(
            $userId,
            $workspace,
            [
                new SectionBlock('To view a list of available commands type `/bbq help`'),
                new DividerBlock(),
                new HeaderBlock('Queues'),
                ...$this->getQueueBlocks($workspace),
            ]
        );
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

            $blocks[] = new DividerBlock();
        }

        return $blocks;
    }
}

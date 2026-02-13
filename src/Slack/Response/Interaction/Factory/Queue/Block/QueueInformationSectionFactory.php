<?php

declare(strict_types=1);

namespace App\Slack\Response\Interaction\Factory\Queue\Block;

use App\Entity\DeploymentQueue;
use App\Entity\Queue;
use App\Slack\Block\Component\SectionBlock;

class QueueInformationSectionFactory
{
    public function create(Queue $queue): SectionBlock
    {
        return new SectionBlock(trim(sprintf(
            '`%s` queue settings: %s %s %s %s',
            $queue->getName(),
            $this->getMaximumEntriesString($queue),
            $this->getExpiryMinutesString($queue),
            $this->getRepositoriesString($queue),
            $this->getQueueBehaviourString($queue),
        )));
    }

    private function getMaximumEntriesString(Queue $queue): string
    {
        if (!$maximumEntries = $queue->getMaximumEntriesPerUser()) {
            return 'No user limit.';
        }

        return sprintf('Users can join a total of `%d` times.', $maximumEntries);
    }

    private function getExpiryMinutesString(Queue $queue): string
    {
        if (!$expiryMinutes = $queue->getExpiryMinutes()) {
            return 'No maximum reservation time.';
        }

        return sprintf('Maximum reservation time: `%d minutes`.', $expiryMinutes);
    }

    private function getRepositoriesString(Queue $queue): string
    {
        if (!$queue instanceof DeploymentQueue) {
            return '';
        }

        return sprintf('Repositories: %s', $queue->getPrettyRepositoryList());
    }

    private function getQueueBehaviourString(Queue $queue): string
    {
        if (!$queue instanceof DeploymentQueue) {
            return '';
        }

        return sprintf('Queue behaviour: `%s`', $queue->getBehaviour()->value);
    }
}

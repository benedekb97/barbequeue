<?php

declare(strict_types=1);

namespace App\Slack\Response\Interaction\Factory\Queue\Block;

use App\Entity\DeploymentQueue;
use App\Entity\Queue;
use App\Slack\Block\Component\Table\RawTextCell;
use App\Slack\Block\Component\Table\TableRow;
use App\Slack\Block\Component\TableBlock;

readonly class QueueInformationTableFactory
{
    public function create(Queue $queue): TableBlock
    {
        return new TableBlock(array_filter([
            new TableRow([
                new RawTextCell('Parameter'),
                new RawTextCell('Value'),
            ]),
            new TableRow([
                new RawTextCell('Top of queue expiry (minutes)'),
                new RawTextCell($queue->getExpiryMinutes() ? $queue->getExpiryMinutes().' minutes' : 'No expiry'),
            ]),
            new TableRow([
                new RawTextCell('Maximum entries per user'),
                new RawTextCell(
                    $queue->getMaximumEntriesPerUser()
                        ? $queue->getMaximumEntriesPerUser().' entries'
                        : 'No limit'
                ),
            ]),
            $this->getRepositoriesRow($queue),
            $this->getBehaviourRow($queue),
        ]));
    }

    private function getRepositoriesRow(Queue $queue): ?TableRow
    {
        if (!$queue instanceof DeploymentQueue) {
            return null;
        }

        return new TableRow([
            new RawTextCell('Repositories'),
            new RawTextCell($queue->getRepositoryList()),
        ]);
    }

    private function getBehaviourRow(Queue $queue): ?TableRow
    {
        if (!$queue instanceof DeploymentQueue) {
            return null;
        }

        return new TableRow([
            new RawTextCell('Behaviour'),
            new RawTextCell($queue->getBehaviour()->getName()),
        ]);
    }
}

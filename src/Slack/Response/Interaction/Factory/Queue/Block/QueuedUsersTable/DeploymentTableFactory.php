<?php

declare(strict_types=1);

namespace App\Slack\Response\Interaction\Factory\Queue\Block\QueuedUsersTable;

use App\Entity\Deployment;
use App\Entity\DeploymentQueue;
use App\Slack\Block\Component\Table\LinkCell;
use App\Slack\Block\Component\Table\RawTextCell;
use App\Slack\Block\Component\Table\TableRow;
use App\Slack\Block\Component\Table\UserCell;
use App\Slack\Block\Component\TableBlock;

readonly class DeploymentTableFactory
{
    public function create(DeploymentQueue $queue): TableBlock
    {
        return new TableBlock([
            $this->getHeaderRow(),
            ...$this->getDataRows($queue),
        ]);
    }

    private function getHeaderRow(): TableRow
    {
        return new TableRow([
            new RawTextCell('#'),
            new RawTextCell('User'),
            new RawTextCell('Deploying'),
            new RawTextCell('Repository'),
            new RawTextCell('Link'),
        ]);
    }

    /** @return TableRow[] */
    private function getDataRows(DeploymentQueue $queue): array
    {
        $rows = [];

        $place = 0;

        foreach ($queue->getQueuedUsers() as $deployment) {
            if ($deployment instanceof Deployment) {
                $rows[] = $this->getRow($deployment, ++$place);
            }
        }

        return $rows;
    }

    private function getRow(Deployment $deployment, int $place): TableRow
    {
        return new TableRow([
            new RawTextCell((string) $place),
            new UserCell((string) $deployment->getUser()?->getSlackId()),
            new RawTextCell((string) $deployment->getDescription()),
            new RawTextCell((string) $deployment->getRepository()?->getName()),
            new LinkCell((string) $deployment->getLink(), 'More info'),
        ]);
    }
}

<?php

declare(strict_types=1);

namespace App\Slack\Response\Interaction\Factory\Queue\Block\QueuedUsersTable;

use App\Entity\Queue;
use App\Slack\Block\Component\Table\RawTextCell;
use App\Slack\Block\Component\Table\TableRow;
use App\Slack\Block\Component\Table\UserCell;
use App\Slack\Block\Component\TableBlock;

readonly class QueuedUsersTableFactory
{
    public function create(Queue $queue): TableBlock
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
            new RawTextCell('Name'),
        ]);
    }

    /** @return TableRow[] */
    private function getDataRows(Queue $queue): array
    {
        $rows = [];

        $place = 0;

        foreach ($queue->getSortedUsers() as $user) {
            $rows[] = new TableRow([
                new RawTextCell((string) ++$place),
                new UserCell($user->getUser()?->getSlackId() ?? ''),
            ]);
        }

        return $rows;
    }
}

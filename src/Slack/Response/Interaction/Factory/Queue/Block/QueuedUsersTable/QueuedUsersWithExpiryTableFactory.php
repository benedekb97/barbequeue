<?php

declare(strict_types=1);

namespace App\Slack\Response\Interaction\Factory\Queue\Block\QueuedUsersTable;

use App\Entity\Queue;
use App\Entity\QueuedUser;
use App\Slack\Block\Component\Table\ItalicTextCell;
use App\Slack\Block\Component\Table\RawTextCell;
use App\Slack\Block\Component\Table\TableRow;
use App\Slack\Block\Component\Table\UserCell;
use App\Slack\Block\Component\TableBlock;

readonly class QueuedUsersWithExpiryTableFactory
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
            new RawTextCell('Reservation time'),
        ]);
    }

    /** @return TableRow[] */
    private function getDataRows(Queue $queue): array
    {
        $rows = [];

        $place = 0;

        foreach ($queue->getSortedUsers() as $user) {
            $rows[] = $this->getRowForUser($user, ++$place);
        }

        return $rows;
    }

    private function getRowForUser(QueuedUser $queuedUser, int $place): TableRow
    {
        $row = [];

        $row[] = new RawTextCell((string) $place);
        $row[] = new UserCell($queuedUser->getUser()?->getSlackId() ?? '');
        $row[] = ($expiry = $queuedUser->getExpiryMinutesLeft()) !== null
            ? new RawTextCell(sprintf('%d minutes', $expiry))
            : new ItalicTextCell('Not set');

        return new TableRow($row);
    }
}

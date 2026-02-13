<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Response\Interaction\Factory\Queue\Block\QueuedUsersTable;

use App\Entity\Queue;
use App\Entity\QueuedUser;
use App\Entity\User;
use App\Slack\Response\Interaction\Factory\Queue\Block\QueuedUsersTable\QueuedUsersWithExpiryTableFactory;
use App\Tests\Unit\Slack\WithTableAssertions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(QueuedUsersWithExpiryTableFactory::class)]
class QueuedUsersWithExpiryTableFactoryTest extends KernelTestCase
{
    use WithTableAssertions;

    #[Test]
    public function itShouldCreateTableWithThreeColumns(): void
    {
        $factory = new QueuedUsersWithExpiryTableFactory();

        $user = $this->createMock(User::class);
        $user->expects($this->exactly(2))
            ->method('getSlackId')
            ->willReturn($userId = 'userId');

        $firstQueuedUser = $this->createMock(QueuedUser::class);
        $firstQueuedUser->expects($this->once())
            ->method('getUser')
            ->willReturn($user);

        $firstQueuedUser->expects($this->once())
            ->method('getExpiryMinutesLeft')
            ->willReturn(null);

        $secondQueuedUser = $this->createMock(QueuedUser::class);
        $secondQueuedUser->expects($this->once())
            ->method('getUser')
            ->willReturn($user);

        $secondQueuedUser->expects($this->once())
            ->method('getExpiryMinutesLeft')
            ->willReturn($minutesLeft = 20);

        $queue = $this->createMock(Queue::class);
        $queue->expects($this->once())
            ->method('getSortedUsers')
            ->willReturn([$firstQueuedUser, $secondQueuedUser]);

        $result = $factory->create($queue)->toArray();

        $table = $this->assertTableRowCount($result, 3);

        $header = $this->getTableRow($table, 0);
        $this->assertRawTextCell($this->getRowCell($header, 0), '#');
        $this->assertRawTextCell($this->getRowCell($header, 1), 'Name');
        $this->assertRawTextCell($this->getRowCell($header, 2), 'Reservation time');

        $firstUserRow = $this->getTableRow($table, 1);
        $this->assertRawTextCell($this->getRowCell($firstUserRow, 0), '1');
        $this->assertUserCell($this->getRowCell($firstUserRow, 1), $userId);
        $this->assertItalicTextCell($this->getRowCell($firstUserRow, 2), 'Not set');

        $secondUserRow = $this->getTableRow($table, 2);
        $this->assertRawTextCell($this->getRowCell($secondUserRow, 0), '2');
        $this->assertUserCell($this->getRowCell($secondUserRow, 1), $userId);
        $this->assertRawTextCell($this->getRowCell($secondUserRow, 2), $minutesLeft.' minutes');
    }
}

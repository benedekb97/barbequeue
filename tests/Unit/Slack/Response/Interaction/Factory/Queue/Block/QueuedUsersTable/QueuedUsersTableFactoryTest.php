<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Response\Interaction\Factory\Queue\Block\QueuedUsersTable;

use App\Entity\Queue;
use App\Entity\QueuedUser;
use App\Entity\User;
use App\Slack\Response\Interaction\Factory\Queue\Block\QueuedUsersTable\QueuedUsersTableFactory;
use App\Tests\Unit\Slack\WithTableAssertions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(QueuedUsersTableFactory::class)]
class QueuedUsersTableFactoryTest extends KernelTestCase
{
    use WithTableAssertions;

    #[Test]
    public function itShouldReturnTableWithTwoColumns(): void
    {
        $factory = new QueuedUsersTableFactory();

        $user = $this->createMock(User::class);
        $user->expects($this->once())
            ->method('getSlackId')
            ->willReturn($userId = 'userId');

        $queuedUser = $this->createMock(QueuedUser::class);
        $queuedUser->expects($this->once())
            ->method('getUser')
            ->willReturn($user);

        $queue = $this->createMock(Queue::class);
        $queue->expects($this->once())
            ->method('getSortedUsers')
            ->willReturn([$queuedUser]);

        $result = $factory->create($queue)->toArray();

        $table = $this->assertTableRowCount($result, 2);

        $header = $this->getTableRow($table, 0);
        $this->assertRawTextCell($this->getRowCell($header, 0), '#');
        $this->assertRawTextCell($this->getRowCell($header, 1), 'Name');

        $data = $this->getTableRow($table, 1);
        $this->assertRawTextCell($this->getRowCell($data, 0), '1');
        $this->assertUserCell($this->getRowCell($data, 1), $userId);
    }
}

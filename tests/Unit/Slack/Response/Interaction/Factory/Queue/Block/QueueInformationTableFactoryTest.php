<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Response\Interaction\Factory\Queue\Block;

use App\Entity\DeploymentQueue;
use App\Entity\Queue;
use App\Enum\QueueBehaviour;
use App\Slack\Response\Interaction\Factory\Queue\Block\QueueInformationTableFactory;
use App\Tests\Unit\Slack\WithTableAssertions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(QueueInformationTableFactory::class)]
class QueueInformationTableFactoryTest extends KernelTestCase
{
    use WithTableAssertions;

    #[Test]
    public function itShouldReturnCorrectlyFormattedTableBlock(): void
    {
        $queue = $this->createMock(Queue::class);
        $queue->expects($this->exactly(2))
            ->method('getExpiryMinutes')
            ->willReturn($expiryMinutes = 5);

        $queue->expects($this->exactly(2))
            ->method('getMaximumEntriesPerUser')
            ->willReturn($maximumEntries = 10);

        $factory = new QueueInformationTableFactory();

        $result = $factory->create($queue)->toArray();

        $rows = $this->assertTableRowCount($result, 3);

        $header = $this->getTableRow($rows, 0);
        $this->assertRawTextCell($this->getRowCell($header, 0), 'Parameter');
        $this->assertRawTextCell($this->getRowCell($header, 1), 'Value');

        $expiry = $this->getTableRow($rows, 1);
        $this->assertRawTextCell($this->getRowCell($expiry, 0), 'Top of queue expiry (minutes)');
        $this->assertRawTextCell($this->getRowCell($expiry, 1), "$expiryMinutes minutes");

        $maxEntriesRow = $this->getTableRow($rows, 2);
        $this->assertRawTextCell($this->getRowCell($maxEntriesRow, 0), 'Maximum entries per user');
        $this->assertRawTextCell($this->getRowCell($maxEntriesRow, 1), "$maximumEntries entries");
    }

    #[Test]
    public function itShouldHaveRepositoriesRowIfDeploymentQueue(): void
    {
        $queue = $this->createMock(DeploymentQueue::class);
        $queue->expects($this->exactly(2))
            ->method('getExpiryMinutes')
            ->willReturn($expiryMinutes = 5);

        $queue->expects($this->exactly(2))
            ->method('getMaximumEntriesPerUser')
            ->willReturn($maximumEntries = 10);

        $queue->expects($this->once())
            ->method('getRepositoryList')
            ->willReturn('repositoryList');

        $queue->expects($this->once())
            ->method('getBehaviour')
            ->willReturn(QueueBehaviour::ALLOW_SIMULTANEOUS);

        $factory = new QueueInformationTableFactory();

        $result = $factory->create($queue)->toArray();

        $rows = $this->assertTableRowCount($result, 5);

        $header = $this->getTableRow($rows, 0);
        $this->assertRawTextCell($this->getRowCell($header, 0), 'Parameter');
        $this->assertRawTextCell($this->getRowCell($header, 1), 'Value');

        $expiry = $this->getTableRow($rows, 1);
        $this->assertRawTextCell($this->getRowCell($expiry, 0), 'Top of queue expiry (minutes)');
        $this->assertRawTextCell($this->getRowCell($expiry, 1), "$expiryMinutes minutes");

        $maxEntriesRow = $this->getTableRow($rows, 2);
        $this->assertRawTextCell($this->getRowCell($maxEntriesRow, 0), 'Maximum entries per user');
        $this->assertRawTextCell($this->getRowCell($maxEntriesRow, 1), "$maximumEntries entries");

        $repositoriesRow = $this->getTableRow($rows, 3);
        $this->assertRawTextCell($this->getRowCell($repositoriesRow, 0), 'Repositories');
        $this->assertRawTextCell($this->getRowCell($repositoriesRow, 1), 'repositoryList');

        $behaviourRow = $this->getTableRow($rows, 4);
        $this->assertRawTextCell($this->getRowCell($behaviourRow, 0), 'Behaviour');
        $this->assertRawTextCell($this->getRowCell($behaviourRow, 1), QueueBehaviour::ALLOW_SIMULTANEOUS->getName());
    }
}

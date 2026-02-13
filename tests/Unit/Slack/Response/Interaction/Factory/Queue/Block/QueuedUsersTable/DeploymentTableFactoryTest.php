<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Response\Interaction\Factory\Queue\Block\QueuedUsersTable;

use App\Entity\Deployment;
use App\Entity\DeploymentQueue;
use App\Entity\Repository;
use App\Entity\User;
use App\Slack\Response\Interaction\Factory\Queue\Block\QueuedUsersTable\DeploymentTableFactory;
use App\Tests\Unit\Slack\WithTableAssertions;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(DeploymentTableFactory::class)]
class DeploymentTableFactoryTest extends KernelTestCase
{
    use WithTableAssertions;

    #[Test]
    public function itShouldCreateTableWithFiveColumns(): void
    {
        $queue = $this->createMock(DeploymentQueue::class);
        $queue->expects($this->once())
            ->method('getQueuedUsers')
            ->willReturn(new ArrayCollection([$deployment = $this->createMock(Deployment::class)]));

        $deployment->expects($this->once())
            ->method('getUser')
            ->willReturn($user = $this->createMock(User::class));

        $deployment->expects($this->once())
            ->method('getDescription')
            ->willReturn($description = 'description');

        $deployment->expects($this->once())
            ->method('getRepository')
            ->willReturn($repository = $this->createMock(Repository::class));

        $deployment->expects($this->once())
            ->method('getLink')
            ->willReturn($link = 'link');

        $user->expects($this->once())
            ->method('getSlackId')
            ->willReturn('slackId');

        $repository->expects($this->once())
            ->method('getName')
            ->willReturn('repositoryName');

        $factory = new DeploymentTableFactory();
        $result = $factory->create($queue)->toArray();

        $rows = $this->assertTableRowCount($result, 2);

        $headerRow = $this->getTableRow($rows, 0);
        $this->assertRawTextCell($this->getRowCell($headerRow, 0), '#');
        $this->assertRawTextCell($this->getRowCell($headerRow, 1), 'User');
        $this->assertRawTextCell($this->getRowCell($headerRow, 2), 'Deploying');
        $this->assertRawTextCell($this->getRowCell($headerRow, 3), 'Repository');
        $this->assertRawTextCell($this->getRowCell($headerRow, 4), 'Link');

        $userRow = $this->getTableRow($rows, 1);
        $this->assertRawTextCell($this->getRowCell($userRow, 0), '1');
        $this->assertUserCell($this->getRowCell($userRow, 1), 'slackId');
        $this->assertRawTextCell($this->getRowCell($userRow, 2), 'description');
        $this->assertRawTextCell($this->getRowCell($userRow, 3), 'repositoryName');
        $this->assertLinkCell($this->getRowCell($userRow, 4), 'link', 'More info');
    }
}

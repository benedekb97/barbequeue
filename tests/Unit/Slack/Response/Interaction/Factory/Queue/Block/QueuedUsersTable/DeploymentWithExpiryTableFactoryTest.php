<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Response\Interaction\Factory\Queue\Block\QueuedUsersTable;

use App\Entity\Deployment;
use App\Entity\DeploymentQueue;
use App\Entity\Repository;
use App\Entity\User;
use App\Slack\Response\Interaction\Factory\Queue\Block\QueuedUsersTable\DeploymentWithExpiryTableFactory;
use App\Tests\Unit\Slack\WithTableAssertions;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(DeploymentWithExpiryTableFactory::class)]
class DeploymentWithExpiryTableFactoryTest extends KernelTestCase
{
    use WithTableAssertions;

    #[Test]
    public function itShouldCreateTableWithSixColumns(): void
    {
        $queue = $this->createMock(DeploymentQueue::class);
        $queue->expects($this->once())
            ->method('getQueuedUsers')
            ->willReturn(new ArrayCollection([
                $deployment = $this->createMock(Deployment::class),
                $secondDeployment = $this->createMock(Deployment::class),
            ]));

        $deployment->expects($this->once())
            ->method('getUser')
            ->willReturn($user = $this->createMock(User::class));

        $deployment->expects($this->once())
            ->method('getDescription')
            ->willReturn('description');

        $deployment->expects($this->once())
            ->method('getRepository')
            ->willReturn($repository = $this->createMock(Repository::class));

        $deployment->expects($this->once())
            ->method('getLink')
            ->willReturn('link');

        $deployment->expects($this->once())
            ->method('getExpiryMinutesLeft')
            ->willReturn(null);

        $secondDeployment->expects($this->once())
            ->method('getUser')
            ->willReturn($user);

        $secondDeployment->expects($this->once())
            ->method('getDescription')
            ->willReturn('description');

        $secondDeployment->expects($this->once())
            ->method('getRepository')
            ->willReturn($repository);

        $secondDeployment->expects($this->once())
            ->method('getLink')
            ->willReturn('link');

        $secondDeployment->expects($this->once())
            ->method('getExpiryMinutesLeft')
            ->willReturn(10);

        $user->expects($this->exactly(2))
            ->method('getSlackId')
            ->willReturn('slackId');

        $repository->expects($this->exactly(2))
            ->method('getName')
            ->willReturn('repositoryName');

        $factory = new DeploymentWithExpiryTableFactory();
        $result = $factory->create($queue)->toArray();

        $rows = $this->assertTableRowCount($result, 3);

        $headerRow = $this->getTableRow($rows, 0);
        $this->assertRawTextCell($this->getRowCell($headerRow, 0), '#');
        $this->assertRawTextCell($this->getRowCell($headerRow, 1), 'User');
        $this->assertRawTextCell($this->getRowCell($headerRow, 2), 'Deploying');
        $this->assertRawTextCell($this->getRowCell($headerRow, 3), 'Repository');
        $this->assertRawTextCell($this->getRowCell($headerRow, 4), 'Link');
        $this->assertRawTextCell($this->getRowCell($headerRow, 5), 'Reservation time');

        $userRow = $this->getTableRow($rows, 1);
        $this->assertRawTextCell($this->getRowCell($userRow, 0), '1');
        $this->assertUserCell($this->getRowCell($userRow, 1), 'slackId');
        $this->assertRawTextCell($this->getRowCell($userRow, 2), 'description');
        $this->assertRawTextCell($this->getRowCell($userRow, 3), 'repositoryName');
        $this->assertLinkCell($this->getRowCell($userRow, 4), 'link', 'More info');
        $this->assertItalicTextCell($this->getRowCell($userRow, 5), 'Not set');

        $userRow = $this->getTableRow($rows, 2);
        $this->assertRawTextCell($this->getRowCell($userRow, 0), '2');
        $this->assertUserCell($this->getRowCell($userRow, 1), 'slackId');
        $this->assertRawTextCell($this->getRowCell($userRow, 2), 'description');
        $this->assertRawTextCell($this->getRowCell($userRow, 3), 'repositoryName');
        $this->assertLinkCell($this->getRowCell($userRow, 4), 'link', 'More info');
        $this->assertRawTextCell($this->getRowCell($userRow, 5), '10 minutes');
    }
}

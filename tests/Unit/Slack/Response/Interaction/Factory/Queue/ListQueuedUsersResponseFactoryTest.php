<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Response\Interaction\Factory\Queue;

use App\Entity\Queue;
use App\Slack\Block\Component\TableBlock;
use App\Slack\Response\Interaction\Factory\Queue\Block\QueuedUsersTable\DeploymentTableFactory;
use App\Slack\Response\Interaction\Factory\Queue\Block\QueuedUsersTable\DeploymentWithExpiryTableFactory;
use App\Slack\Response\Interaction\Factory\Queue\Block\QueuedUsersTable\QueuedUsersTableFactory;
use App\Slack\Response\Interaction\Factory\Queue\Block\QueuedUsersTable\QueuedUsersWithExpiryTableFactory;
use App\Slack\Response\Interaction\Factory\Queue\ListQueuedUsersResponseFactory;
use App\Tests\Unit\Slack\WithBlockAssertions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(ListQueuedUsersResponseFactory::class)]
class ListQueuedUsersResponseFactoryTest extends KernelTestCase
{
    use WithBlockAssertions;

    #[Test]
    public function itShouldCreateQueuedUsersTableResponseIfQueueNotHasUserWithExpiryMinutes(): void
    {
        $queue = $this->createMock(Queue::class);
        $queue->expects($this->once())
            ->method('hasQueuedUserWithExpiryMinutes')
            ->willReturn(false);

        $queue->expects($this->once())
            ->method('getName')
            ->willReturn('queueName');

        $tableBlock = $this->createMock(TableBlock::class);
        $tableBlock->expects($this->once())
            ->method('toArray')
            ->willReturn($table = []);

        $expiryTableFactory = $this->createMock(QueuedUsersWithExpiryTableFactory::class);
        $expiryTableFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $tableFactory = $this->createMock(QueuedUsersTableFactory::class);
        $tableFactory->expects($this->once())
            ->method('create')
            ->with($queue)
            ->willReturn($tableBlock);

        $deploymentTableFactory = $this->createMock(DeploymentTableFactory::class);
        $deploymentTableFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $deploymentExpiryTableFactory = $this->createMock(DeploymentWithExpiryTableFactory::class);
        $deploymentExpiryTableFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $factory = new ListQueuedUsersResponseFactory(
            $tableFactory,
            $expiryTableFactory,
            $deploymentTableFactory,
            $deploymentExpiryTableFactory,
        );

        $result = $factory->create($queue)->toArray();

        $this->assertArrayHasKey('blocks', $result);
        $this->assertIsArray($blocks = $result['blocks']);
        $this->assertCount(2, $blocks);
        $this->assertSectionBlockCorrectlyFormatted(
            'Users currently in the `queueName` queue',
            $blocks[0],
        );
        $this->assertEquals($table, $blocks[1]);
    }

    #[Test]
    public function itShouldCreateQueuedUsersWithExpiryTableIfQueueHasUserWithExpiryMinutes(): void
    {
        $queue = $this->createMock(Queue::class);
        $queue->expects($this->once())
            ->method('hasQueuedUserWithExpiryMinutes')
            ->willReturn(true);

        $queue->expects($this->once())
            ->method('getName')
            ->willReturn('queueName');

        $tableBlock = $this->createMock(TableBlock::class);
        $tableBlock->expects($this->once())
            ->method('toArray')
            ->willReturn($table = []);

        $deploymentTableFactory = $this->createMock(DeploymentTableFactory::class);
        $deploymentTableFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $deploymentExpiryTableFactory = $this->createMock(DeploymentWithExpiryTableFactory::class);
        $deploymentExpiryTableFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $expiryTableFactory = $this->createMock(QueuedUsersWithExpiryTableFactory::class);
        $expiryTableFactory->expects($this->once())
            ->method('create')
            ->with($queue)
            ->willReturn($tableBlock);

        $tableFactory = $this->createMock(QueuedUsersTableFactory::class);
        $tableFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $factory = new ListQueuedUsersResponseFactory(
            $tableFactory,
            $expiryTableFactory,
            $deploymentTableFactory,
            $deploymentExpiryTableFactory,
        );

        $result = $factory->create($queue)->toArray();

        $this->assertArrayHasKey('blocks', $result);
        $this->assertIsArray($blocks = $result['blocks']);
        $this->assertCount(2, $blocks);
        $this->assertSectionBlockCorrectlyFormatted(
            'Users currently in the `queueName` queue',
            $blocks[0],
        );
        $this->assertEquals($table, $blocks[1]);
    }
}

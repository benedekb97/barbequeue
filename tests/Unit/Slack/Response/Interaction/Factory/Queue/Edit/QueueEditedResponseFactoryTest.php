<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Response\Interaction\Factory\Queue\Edit;

use App\Entity\Queue;
use App\Slack\Block\Component\ActionsBlock;
use App\Slack\Block\Component\TableBlock;
use App\Slack\Response\Interaction\Factory\Queue\Block\AdministratorQueueActionsFactory;
use App\Slack\Response\Interaction\Factory\Queue\Block\QueueInformationTableFactory;
use App\Slack\Response\Interaction\Factory\Queue\Edit\QueueEditedResponseFactory;
use App\Tests\Unit\Slack\WithBlockAssertions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(QueueEditedResponseFactory::class)]
class QueueEditedResponseFactoryTest extends KernelTestCase
{
    use WithBlockAssertions;

    #[Test]
    public function itShouldReturnPrivateMessageResponse(): void
    {
        $queue = $this->createMock(Queue::class);
        $queue->expects($this->once())
            ->method('getName')
            ->willReturn('queueName');

        $tableBlock = $this->createMock(TableBlock::class);
        $tableBlock->expects($this->once())
            ->method('toArray')
            ->willReturn($table = ['table']);

        $tableFactory = $this->createMock(QueueInformationTableFactory::class);
        $tableFactory->expects($this->once())
            ->method('create')
            ->with($queue)
            ->willReturn($tableBlock);

        $actionsBlock = $this->createMock(ActionsBlock::class);
        $actionsBlock->expects($this->once())
            ->method('toArray')
            ->willReturn($actions = ['actions']);

        $actionsFactory = $this->createMock(AdministratorQueueActionsFactory::class);
        $actionsFactory->expects($this->once())
            ->method('create')
            ->with($queue)
            ->willReturn($actionsBlock);

        $factory = new QueueEditedResponseFactory($tableFactory, $actionsFactory);

        $result = $factory->create($queue);

        $result = $result->toArray();

        $this->assertArrayHasKey('blocks', $result);
        $this->assertIsArray($blocks = $result['blocks']);
        $this->assertCount(3, $blocks);

        $this->assertSectionBlockCorrectlyFormatted(
            'Queue `queueName` edited successfully.',
            $blocks[0],
        );

        $this->assertEquals($table, $blocks[1]);
        $this->assertEquals($actions, $blocks[2]);
    }
}

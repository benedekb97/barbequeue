<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Response\Interaction\Factory\Queue\Block;

use App\Entity\Queue;
use App\Repository\QueueRepositoryInterface;
use App\Slack\Block\Component\ActionsBlock;
use App\Slack\Response\Interaction\Factory\Queue\Block\UnrecognisedQueueActionsBlockFactory;
use App\Tests\Unit\Slack\WithBlockAssertions;
use App\Tests\Unit\Slack\WithBlockElementAssertions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(UnrecognisedQueueActionsBlockFactory::class)]
class UnrecognisedQueueActionsBlockFactoryTest extends KernelTestCase
{
    use WithBlockAssertions;
    use WithBlockElementAssertions;

    #[Test]
    public function itShouldReturnNullIfUserIdPassedIsNull(): void
    {
        $repository = $this->createStub(QueueRepositoryInterface::class);

        $factory = new UnrecognisedQueueActionsBlockFactory($repository);

        $result = $factory->create('teamId', null);

        $this->assertNull($result);
    }

    #[Test]
    public function itShouldReturnNullIfNoQueuesFound(): void
    {
        $repository = $this->createMock(QueueRepositoryInterface::class);
        $repository->expects($this->once())
            ->method('findByTeamId')
            ->with($teamId = 'teamId')
            ->willReturn([]);

        $factory = new UnrecognisedQueueActionsBlockFactory($repository);

        $result = $factory->create($teamId, 'userId');

        $this->assertNull($result);
    }

    #[Test]
    public function itShouldReturnNullIfNoButtonsToDisplay(): void
    {
        $firstQueue = $this->createMock(Queue::class);
        $firstQueue->expects($this->once())
            ->method('canLeave')
            ->with($userId = 'userId')
            ->willReturn(false);

        $firstQueue->expects($this->once())
            ->method('canRelease')
            ->with($userId)
            ->willReturn(false);

        $firstQueue->expects($this->once())
            ->method('canJoin')
            ->with($userId)
            ->willReturn(false);

        $firstQueue->expects($this->never())
            ->method('getId')
            ->withAnyParameters();

        $firstQueue->expects($this->never())
            ->method('getName')
            ->withAnyParameters();

        $secondQueue = $this->createMock(Queue::class);
        $secondQueue->expects($this->once())
            ->method('canJoin')
            ->with($userId)
            ->willReturn(false);

        $secondQueue->expects($this->once())
            ->method('canRelease')
            ->with($userId)
            ->willReturn(false);

        $secondQueue->expects($this->once())
            ->method('canLeave')
            ->with($userId)
            ->willReturn(false);

        $secondQueue->expects($this->never())
            ->method('getId')
            ->withAnyParameters();

        $secondQueue->expects($this->never())
            ->method('getName')
            ->withAnyParameters();

        $repository = $this->createMock(QueueRepositoryInterface::class);
        $repository->expects($this->once())
            ->method('findByTeamId')
            ->with($teamId = 'teamId')
            ->willReturn([$firstQueue, $secondQueue]);

        $factory = new UnrecognisedQueueActionsBlockFactory($repository);

        $result = $factory->create($teamId, $userId);

        $this->assertNull($result);
    }

    #[Test]
    public function itShouldReturnActionsBlock(): void
    {
        $firstQueue = $this->createMock(Queue::class);
        $firstQueue->expects($this->once())
            ->method('canLeave')
            ->with($userId = 'userId')
            ->willReturn(false);

        $firstQueue->expects($this->once())
            ->method('canRelease')
            ->with($userId)
            ->willReturn(false);

        $firstQueue->expects($this->once())
            ->method('canJoin')
            ->with($userId)
            ->willReturn(true);

        $firstQueue->expects($this->once())
            ->method('getId')
            ->willReturn($firstQueueId = 1);

        $firstQueue->expects($this->once())
            ->method('getName')
            ->willReturn($firstQueueName = 'firstQueueName');

        $secondQueue = $this->createMock(Queue::class);
        $secondQueue->expects($this->once())
            ->method('canJoin')
            ->with($userId)
            ->willReturn(true);

        $secondQueue->expects($this->once())
            ->method('canLeave')
            ->with($userId)
            ->willReturn(true);

        $secondQueue->expects($this->once())
            ->method('canRelease')
            ->with($userId)
            ->willReturn(true);

        $secondQueue->expects($this->exactly(3))
            ->method('getId')
            ->willReturn($secondQueueId = 2);

        $secondQueue->expects($this->exactly(3))
            ->method('getName')
            ->willReturn($secondQueueName = 'secondQueueName');

        $repository = $this->createMock(QueueRepositoryInterface::class);
        $repository->expects($this->once())
            ->method('findByTeamId')
            ->with($teamId = 'teamId')
            ->willReturn([$firstQueue, $secondQueue]);

        $factory = new UnrecognisedQueueActionsBlockFactory($repository);

        $result = $factory->create($teamId, $userId);

        $this->assertInstanceOf(ActionsBlock::class, $result);

        $result = $result->toArray();

        $this->assertActionsBlockCorrectlyFormatted(
            [],
            $result,
            'unrecognised_queue_action',
            true,
        );

        $this->assertArrayHasKey('elements', $result);
        $this->assertIsArray($elements = $result['elements']);
        $this->assertCount(4, $elements);
        $this->assertButtonBlockElementCorrectlyFormatted(
            'Join '.$firstQueueName.' queue',
            $elements[0],
            'join-queue-'.$firstQueueId,
            expectedValue: $firstQueueName,
        );

        $this->assertButtonBlockElementCorrectlyFormatted(
            'Join '.$secondQueueName.' queue',
            $elements[1],
            'join-queue-'.$secondQueueId,
            expectedValue: $secondQueueName,
        );

        $this->assertButtonBlockElementCorrectlyFormatted(
            'Release '.$secondQueueName.' queue',
            $elements[2],
            'release-queue-'.$secondQueueId,
            expectedValue: $secondQueueName,
        );

        $this->assertButtonBlockElementCorrectlyFormatted(
            'Leave '.$secondQueueName.' queue',
            $elements[3],
            'leave-queue-'.$secondQueueId,
            expectedValue: $secondQueueName,
        );
    }
}

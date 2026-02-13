<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Response\Interaction\Factory\Queue\Release;

use App\Entity\Queue;
use App\Entity\QueuedUser;
use App\Slack\Response\Interaction\Factory\Queue\Free\QueueReleasedResponseFactory;
use App\Tests\Unit\Slack\WithBlockAssertions;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(QueueReleasedResponseFactory::class)]
class QueueReleasedResponseFactoryTest extends KernelTestCase
{
    use WithBlockAssertions;

    #[Test]
    public function itShouldCreateSingleSectionResponseIfUserCannotLeaveAgain(): void
    {
        $queue = $this->createMock(Queue::class);
        $queue->expects($this->once())
            ->method('canLeave')
            ->with($userId = 'userId')
            ->willReturn(false);

        $queue->expects($this->once())
            ->method('getName')
            ->willReturn('queueName');

        $factory = new QueueReleasedResponseFactory();

        $result = $factory->create($queue, $userId)->toArray();

        $this->assertArrayHasKey('blocks', $result);
        $this->assertIsArray($blocks = $result['blocks']);
        $this->assertCount(1, $blocks);
        $this->assertSectionBlockCorrectlyFormatted(
            'You have left the front of the `queueName` queue.',
            $blocks[0],
        );
    }

    #[Test]
    public function itShouldCreateDoubleSectionResponseIfUserIsStillInQueue(): void
    {
        $queuedUser = $this->createStub(QueuedUser::class);

        $collection = $this->createMock(Collection::class);
        $collection->expects($this->once())
            ->method('toArray')
            ->willReturn([$queuedUser]);

        $queue = $this->createMock(Queue::class);
        $queue->expects($this->once())
            ->method('getQueuedUsers')
            ->willReturn($collection);

        $queue->expects($this->once())
            ->method('canLeave')
            ->with($userId = 'userId')
            ->willReturn(true);

        $queue->expects($this->once())
            ->method('getName')
            ->willReturn('queueName');

        $queuedUsersByUserId = $this->createMock(Collection::class);
        $queuedUsersByUserId->expects($this->once())
            ->method('contains')
            ->with($queuedUser)
            ->willReturn(true);

        $queue->expects($this->once())
            ->method('getQueuedUsersByUserId')
            ->with($userId)
            ->willReturn($queuedUsersByUserId);

        $factory = new QueueReleasedResponseFactory();

        $result = $factory->create($queue, $userId)->toArray();

        $this->assertArrayHasKey('blocks', $result);
        $this->assertIsArray($blocks = $result['blocks']);
        $this->assertCount(2, $blocks);
        $this->assertSectionBlockCorrectlyFormatted(
            'You have left the front of the `queueName` queue.',
            $blocks[0],
        );
        $this->assertSectionBlockCorrectlyFormatted(
            'You are now 1st in the `queueName` queue.',
            $blocks[1],
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Response\Interaction\Factory\Queue\Leave;

use App\Entity\Queue;
use App\Entity\QueuedUser;
use App\Slack\Response\Interaction\Factory\Queue\Leave\QueueLeftResponseFactory;
use App\Tests\Unit\Slack\WithBlockAssertions;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(QueueLeftResponseFactory::class)]
class QueueLeftResponseFactoryTest extends KernelTestCase
{
    use WithBlockAssertions;

    #[Test]
    public function itShouldReturnInteractionResponseWithOneSectionIfUserCannotLeaveAgain(): void
    {
        $queue = $this->createMock(Queue::class);
        $queue->expects($this->once())
            ->method('canLeave')
            ->with($userId = 'userId')
            ->willReturn(false);

        $queue->expects($this->once())
            ->method('getName')
            ->willReturn($queueName = 'queueName');

        $factory = new QueueLeftResponseFactory();
        $response = $factory->create($queue, $userId)->toArray();

        $this->assertArrayHasKey('blocks', $response);
        $this->assertIsArray($blocks = $response['blocks']);
        $this->assertCount(1, $blocks);

        $this->assertSectionBlockCorrectlyFormatted(
            'You have left the `'.$queueName.'` queue.',
            $blocks[0]
        );
    }

    #[Test]
    public function itShouldReturnInteractionResponseWithThreeBlocksIfUserCanLeaveAgain(): void
    {
        $allUsers = $this->createMock(Collection::class);
        $allUsers->expects($this->once())
            ->method('toArray')
            ->willReturn([
                $this->createStub(QueuedUser::class),
                $this->createStub(QueuedUser::class),
            ]);

        $placements = $this->createMock(Collection::class);
        $placements->expects($this->exactly(2))
            ->method('contains')
            ->withAnyParameters()
            ->willReturn(true);

        $queue = $this->createMock(Queue::class);
        $queue->expects($this->once())
            ->method('canLeave')
            ->with($userId = 'userId')
            ->willReturn(true);

        $queue->expects($this->exactly(2))
            ->method('getName')
            ->willReturn($queueName = 'queueName');

        $queue->expects($this->once())
            ->method('getQueuedUsers')
            ->willReturn($allUsers);

        $queue->expects($this->once())
            ->method('getQueuedUsersByUserId')
            ->with($userId)
            ->willReturn($placements);

        $factory = new QueueLeftResponseFactory();

        $response = $factory->create($queue, $userId)->toArray();

        $this->assertArrayHasKey('blocks', $response);
        $this->assertIsArray($blocks = $response['blocks']);
        $this->assertCount(2, $blocks);

        $this->assertSectionBlockCorrectlyFormatted(
            'You have been removed from the `'.$queueName.'` queue.',
            $blocks[0],
        );
        $this->assertSectionBlockCorrectlyFormatted(
            'You are now 1st and 2nd in the `'.$queueName.'` queue.',
            $blocks[1],
        );
    }
}

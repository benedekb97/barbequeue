<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Response\Common\Factory;

use App\Entity\Queue;
use App\Entity\QueuedUser;
use App\Entity\User;
use App\Slack\Response\PrivateMessage\Factory\RemovedFromQueueMessageFactory;
use App\Tests\Unit\Slack\WithBlockAssertions;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(RemovedFromQueueMessageFactory::class)]
class RemovedFromQueueMessageFactoryTest extends KernelTestCase
{
    use WithBlockAssertions;

    #[Test, DataProvider('provideMessages')]
    public function itShouldReturnSingleSectionIfUserHasNoOtherPlacesInQueue(
        bool $automatic,
        string $message,
        string $queueName,
    ): void {
        $user = $this->createMock(User::class);
        $user->expects($this->once())
            ->method('getSlackId')
            ->willReturn($userId = 'userId');

        $queuedUser = $this->createMock(QueuedUser::class);
        $queuedUser->expects($this->exactly(2))
            ->method('getUser')
            ->willReturn($user);

        $queue = $this->createMock(Queue::class);
        $queue->expects($this->once())
            ->method('canLeave')
            ->with($userId)
            ->willReturn(false);

        $queue->expects($this->once())
            ->method('getName')
            ->willReturn($queueName);

        $factory = new RemovedFromQueueMessageFactory();

        $response = $factory->create($queuedUser, $queue, $automatic)->toArray();

        $this->assertArrayHasKey('blocks', $response);
        $this->assertIsString($response['blocks']);

        $blocks = json_decode($response['blocks'], true);

        $this->assertIsArray($blocks);
        $this->assertCount(1, $blocks);

        $this->assertSectionBlockCorrectlyFormatted($message, $blocks[0]);
    }

    #[Test, DataProvider('provideMessages')]
    public function itShouldReturnThreeBlocksIfUserHasOtherPlacesInQueue(
        bool $automatic,
        string $message,
        string $queueName,
    ): void {
        $user = $this->createMock(User::class);
        $user->expects($this->exactly(2))
            ->method('getSlackId')
            ->willReturn($userId = 'userId');

        $queuedUser = $this->createMock(QueuedUser::class);
        $queuedUser->expects($this->exactly(3))
            ->method('getUser')
            ->willReturn($user);

        $stillQueuedUser = $this->createStub(QueuedUser::class);

        $collection = $this->createMock(Collection::class);
        $collection->expects($this->once())
            ->method('toArray')
            ->willReturn([$stillQueuedUser]);

        $queue = $this->createMock(Queue::class);
        $queue->expects($this->once())
            ->method('canLeave')
            ->with($userId)
            ->willReturn(true);

        $queue->expects($this->exactly(2))
            ->method('getName')
            ->willReturn($queueName);

        $queue->expects($this->once())
            ->method('getQueuedUsers')
            ->willReturn($collection);

        $queuedUsersCollection = $this->createMock(Collection::class);
        $queuedUsersCollection->expects($this->once())
            ->method('contains')
            ->with($stillQueuedUser)
            ->willReturn(true);

        $queue->expects($this->once())
            ->method('getQueuedUsersByUserId')
            ->with($userId)
            ->willReturn($queuedUsersCollection);

        $factory = new RemovedFromQueueMessageFactory();

        $response = $factory->create($queuedUser, $queue, $automatic)->toArray();

        $this->assertArrayHasKey('blocks', $response);
        $this->assertIsString($response['blocks']);

        $blocks = json_decode($response['blocks'], true);

        $this->assertIsArray($blocks);
        $this->assertCount(2, $blocks);

        $this->assertSectionBlockCorrectlyFormatted($message, $blocks[0]);
        $this->assertSectionBlockCorrectlyFormatted(
            'You are now 1st in the `queueName` queue.',
            $blocks[1],
        );
    }

    public static function provideMessages(): array
    {
        $queueName = 'queueName';

        return [
            [true, 'Your time at the front of the `'.$queueName.'` queue is up.', $queueName],
            [false, 'You have been removed from the front of the `'.$queueName.'` queue.', $queueName],
        ];
    }
}

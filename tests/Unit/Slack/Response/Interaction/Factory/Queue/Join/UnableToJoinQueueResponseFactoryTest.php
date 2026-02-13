<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Response\Interaction\Factory\Queue\Join;

use App\Entity\Queue;
use App\Slack\Response\Interaction\Factory\Queue\Join\UnableToJoinQueueResponseFactory;
use App\Tests\Unit\Slack\WithBlockAssertions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(UnableToJoinQueueResponseFactory::class)]
class UnableToJoinQueueResponseFactoryTest extends KernelTestCase
{
    use WithBlockAssertions;

    #[Test, DataProvider('provideForItShouldCreateInteractionResponseWithThreeBlocks')]
    public function itShouldCreateInteractionResponseWithThreeBlocks(
        string $expectedMessage,
        int $maximumEntriesPerUser,
        string $queueName,
    ): void {
        $queue = $this->createMock(Queue::class);
        $queue->expects($this->once())
            ->method('getName')
            ->willReturn($queueName);

        $queue->expects($this->exactly(1 === $maximumEntriesPerUser ? 1 : 2))
            ->method('getMaximumEntriesPerUser')
            ->willReturn($maximumEntriesPerUser);

        $factory = new UnableToJoinQueueResponseFactory();
        $response = $factory->create($queue)->toArray();

        $this->assertArrayHasKey('blocks', $response);
        $this->assertIsArray($blocks = $response['blocks']);
        $this->assertCount(1, $blocks);

        $this->assertSectionBlockCorrectlyFormatted($expectedMessage, $blocks[0]);
    }

    public static function provideForItShouldCreateInteractionResponseWithThreeBlocks(): array
    {
        $queueName = 'queueName';

        return [
            ['You are already in the `'.$queueName.'` queue.', 1, $queueName],
            ['You can only join the `'.$queueName.'` queue *3* times.', 3, $queueName],
        ];
    }
}

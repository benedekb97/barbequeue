<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Response\Interaction\Factory\Queue\Leave;

use App\Entity\Queue;
use App\Slack\Response\Interaction\Factory\Queue\Leave\UnableToLeaveQueueResponseFactory;
use App\Tests\Unit\Slack\WithBlockAssertions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(UnableToLeaveQueueResponseFactory::class)]
class UnableToLeaveQueueResponseFactoryTest extends KernelTestCase
{
    use WithBlockAssertions;

    #[Test]
    public function itShouldReturnInteractionResponseWithThreeBlocks(): void
    {
        $queue = $this->createMock(Queue::class);
        $queue->expects($this->once())
            ->method('getName')
            ->willReturn($queueName = 'queueName');

        $factory = new UnableToLeaveQueueResponseFactory();

        $response = $factory->create($queue)->toArray();

        $this->assertArrayHasKey('blocks', $response);
        $this->assertIsArray($blocks = $response['blocks']);
        $this->assertCount(1, $blocks);

        $this->assertSectionBlockCorrectlyFormatted(
            'You are not in the `'.$queueName.'` queue.',
            $blocks[0]
        );
    }
}

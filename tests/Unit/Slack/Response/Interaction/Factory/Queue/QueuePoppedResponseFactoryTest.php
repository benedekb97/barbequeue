<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Response\Interaction\Factory\Queue;

use App\Entity\Queue;
use App\Slack\Response\Interaction\Factory\Queue\QueuePoppedResponseFactory;
use App\Tests\Unit\Slack\WithBlockAssertions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(QueuePoppedResponseFactory::class)]
class QueuePoppedResponseFactoryTest extends KernelTestCase
{
    use WithBlockAssertions;

    #[Test]
    public function itShouldCreateInteractionResponseWithSingleBlock(): void
    {
        $queue = $this->createMock(Queue::class);
        $queue->expects($this->once())
            ->method('getName')
            ->willReturn($queueName = 'queueName');

        $factory = new QueuePoppedResponseFactory();

        $response = $factory->create($queue)->toArray();

        $this->assertArrayHasKey('blocks', $response);
        $this->assertIsArray($blocks = $response['blocks']);
        $this->assertCount(1, $blocks);

        $this->assertSectionBlockCorrectlyFormatted(
            'Queue `'.$queueName.'` has been popped.',
            $blocks[0],
        );
    }
}

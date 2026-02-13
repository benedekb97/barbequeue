<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Response\Interaction\Factory\Queue\Add;

use App\Entity\Queue;
use App\Slack\Response\Interaction\Factory\Queue\Add\QueueAddedResponseFactory;
use App\Tests\Unit\Slack\WithBlockAssertions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(QueueAddedResponseFactory::class)]
class QueueAddedResponseFactoryTest extends KernelTestCase
{
    use WithBlockAssertions;

    #[Test]
    public function itShouldCreateSlackInteractionResponse(): void
    {
        $queue = $this->createMock(Queue::class);
        $queue->expects($this->once())
            ->method('getName')
            ->willReturn('queueName');

        $factory = new QueueAddedResponseFactory();

        $result = $factory->create($queue)->toArray();

        $this->assertArrayHasKey('blocks', $result);
        $this->assertIsArray($result['blocks']);
        $this->assertCount(1, $result['blocks']);
        $this->assertSectionBlockCorrectlyFormatted(
            'A queue called `queueName` has been created!',
            $result['blocks'][0],
        );
    }
}

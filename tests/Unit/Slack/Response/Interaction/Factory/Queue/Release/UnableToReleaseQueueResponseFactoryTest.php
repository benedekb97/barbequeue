<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Response\Interaction\Factory\Queue\Release;

use App\Entity\Queue;
use App\Slack\Response\Interaction\Factory\Queue\Free\UnableToReleaseQueueResponseFactory;
use App\Tests\Unit\Slack\WithBlockAssertions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(UnableToReleaseQueueResponseFactory::class)]
class UnableToReleaseQueueResponseFactoryTest extends KernelTestCase
{
    use WithBlockAssertions;

    #[Test]
    public function itShouldReturnSlackInteractionResponse(): void
    {
        $queue = $this->createMock(Queue::class);
        $queue->expects($this->once())
            ->method('getName')
            ->willReturn('queueName');

        $factory = new UnableToReleaseQueueResponseFactory();

        $result = $factory->create($queue)->toArray();

        $this->assertArrayHasKey('blocks', $result);
        $this->assertIsArray($blocks = $result['blocks']);
        $this->assertCount(1, $blocks);
        $this->assertSectionBlockCorrectlyFormatted(
            'You are not at the front of the `queueName` queue.',
            $blocks[0],
        );
    }
}

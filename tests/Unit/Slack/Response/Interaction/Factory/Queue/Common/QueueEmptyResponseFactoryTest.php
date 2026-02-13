<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Response\Interaction\Factory\Queue\Common;

use App\Entity\Queue;
use App\Slack\Response\Interaction\Factory\Queue\Common\QueueEmptyResponseFactory;
use App\Tests\Unit\Slack\WithBlockAssertions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(QueueEmptyResponseFactory::class)]
class QueueEmptyResponseFactoryTest extends KernelTestCase
{
    use WithBlockAssertions;

    #[Test]
    public function itShouldCreateResponseWithASingleSectionBlock(): void
    {
        $queue = $this->createMock(Queue::class);
        $queue->expects($this->once())
            ->method('getName')
            ->willReturn($queueName = 'queuename');

        $expectedMessage = 'The `'.$queueName.'` queue is empty!';

        $factory = new QueueEmptyResponseFactory();

        $response = $factory->create($queue)->toArray();

        $this->assertArrayHasKey('blocks', $response);
        $this->assertisArray($blocks = $response['blocks']);
        $this->assertCount(1, $blocks);
        $this->assertSectionBlockCorrectlyFormatted($expectedMessage, $blocks[0]);
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Response\Interaction\Factory\Queue\BlockElement;

use App\Entity\Queue;
use App\Slack\Response\Interaction\Factory\Queue\BlockElement\PopQueueButtonFactory;
use App\Tests\Unit\Slack\WithBlockElementAssertions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(PopQueueButtonFactory::class)]
class PopQueueButtonFactoryTest extends KernelTestCase
{
    use WithBlockElementAssertions;

    #[Test]
    public function itShouldCreateButtonBlockElement(): void
    {
        $queue = $this->createMock(Queue::class);
        $queue->expects($this->once())
            ->method('getId')
            ->willReturn(1);

        $queue->expects($this->once())
            ->method('getName')
            ->willReturn('queueName');

        $factory = new PopQueueButtonFactory();

        $result = $factory->create($queue)->toArray();

        $this->assertButtonBlockElementCorrectlyFormatted(
            'Pop',
            $result,
            'pop-queue-action-1',
            expectedValue: 'queueName',
        );
    }
}

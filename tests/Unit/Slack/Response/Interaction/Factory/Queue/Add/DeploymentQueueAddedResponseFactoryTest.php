<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Response\Interaction\Factory\Queue\Add;

use App\Entity\DeploymentQueue;
use App\Slack\Response\Interaction\Factory\Queue\Add\DeploymentQueueAddedResponseFactory;
use App\Tests\Unit\Slack\WithBlockAssertions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(DeploymentQueueAddedResponseFactory::class)]
class DeploymentQueueAddedResponseFactoryTest extends KernelTestCase
{
    use WithBlockAssertions;

    #[Test]
    public function itShouldReturnSlackInteractionResponse(): void
    {
        $queue = $this->createMock(DeploymentQueue::class);
        $queue->expects($this->once())
            ->method('getName')
            ->willReturn('queueName');

        $factory = new DeploymentQueueAddedResponseFactory();

        $result = $factory->create($queue)->toArray();

        $this->assertArrayHasKey('blocks', $result);
        $this->assertIsArray($blocks = $result['blocks']);
        $this->assertCount(1, $blocks);
        $this->assertSectionBlockCorrectlyFormatted(
            'A deployment queue called `queueName` has been created!',
            $blocks[0],
        );
    }
}

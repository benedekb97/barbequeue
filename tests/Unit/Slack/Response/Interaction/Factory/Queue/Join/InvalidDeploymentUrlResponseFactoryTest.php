<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Response\Interaction\Factory\Queue\Join;

use App\Entity\Queue;
use App\Slack\Response\Interaction\Factory\Queue\Join\InvalidDeploymentUrlResponseFactory;
use App\Tests\Unit\Slack\WithBlockAssertions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(InvalidDeploymentUrlResponseFactory::class)]
class InvalidDeploymentUrlResponseFactoryTest extends KernelTestCase
{
    use WithBlockAssertions;

    #[Test]
    public function itShouldCreateSlackInteraction(): void
    {
        $queue = $this->createMock(Queue::class);
        $queue->expects($this->once())
            ->method('getName')
            ->willReturn('queueName');

        $queue->expects($this->once())
            ->method('getId')
            ->willReturn(1);

        $factory = new InvalidDeploymentUrlResponseFactory();

        $result = $factory->create('deploymentLink', $queue)->toArray();

        $this->assertArrayHasKey('blocks', $result);
        $this->assertIsArray($blocks = $result['blocks']);
        $this->assertCount(1, $blocks);

        $this->assertSectionBlockCorrectlyFormatted(
            'The deployment link you entered was not a valid URL: `deploymentLink`. Please try again.',
            $blocks[0],
            expectedAccessory: [
                'type' => 'button',
                'text' => [
                    'type' => 'plain_text',
                    'text' => 'Join queueName queue',
                ],
                'value' => 'queueName',
                'action_id' => 'join-queue-1',
            ],
        );
    }
}

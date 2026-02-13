<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Response\Interaction\Factory\Queue\BlockElement;

use App\Entity\Queue;
use App\Slack\Response\Interaction\Factory\Queue\BlockElement\EditQueueButtonFactory;
use App\Tests\Unit\Slack\WithBlockElementAssertions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(EditQueueButtonFactory::class)]
class EditQueueButtonFactoryTest extends KernelTestCase
{
    use WithBlockElementAssertions;

    #[Test]
    public function itShouldReturnButtonBlockElement(): void
    {
        $queue = $this->createMock(Queue::class);
        $queue->expects($this->exactly(2))
            ->method('getId')
            ->willReturn(1);

        $factory = new EditQueueButtonFactory();

        $result = $factory->create($queue);

        $this->assertButtonBlockElementCorrectlyFormatted(
            'Edit',
            $result->toArray(),
            'edit-queue-action-1',
            expectedValue: '1',
        );
    }
}

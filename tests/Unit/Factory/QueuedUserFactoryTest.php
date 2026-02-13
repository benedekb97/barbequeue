<?php

declare(strict_types=1);

namespace App\Tests\Unit\Factory;

use App\Entity\Queue;
use App\Entity\QueuedUser;
use App\Factory\QueuedUserFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(QueuedUserFactory::class)]
class QueuedUserFactoryTest extends KernelTestCase
{
    #[Test]
    public function itShouldCreateQueuedUser(): void
    {
        $queue = $this->createMock(Queue::class);
        $queue->expects($this->once())
            ->method('addQueuedUser')
            ->willReturnCallback(function ($argument) use ($queue) {
                $this->assertInstanceOf(QueuedUser::class, $argument);
                $this->assertNotNull($argument->getCreatedAt());

                return $queue;
            });

        $factory = new QueuedUserFactory();
        $queuedUser = $factory->createForQueue($queue);

        $this->assertNotNull($queuedUser->getCreatedAt());
    }
}

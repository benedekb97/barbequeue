<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Queue\Exception;

use App\Entity\Queue;
use App\Service\Queue\Exception\UnableToJoinQueueException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(UnableToJoinQueueException::class)]
class UnableToJoinQueueExceptionTest extends KernelTestCase
{
    #[Test]
    public function itShouldReturnPassedQueue(): void
    {
        $queue = $this->createStub(Queue::class);

        $exception = new UnableToJoinQueueException($queue);

        $this->assertSame($queue, $exception->getQueue());
    }
}

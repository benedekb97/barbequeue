<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Queue\Exception;

use App\Entity\Queue;
use App\Service\Queue\Exception\UnableToLeaveQueueException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(UnableToLeaveQueueException::class)]
class UnableToLeaveQueueExceptionTest extends KernelTestCase
{
    #[Test]
    public function itShouldReturnPassedQueue(): void
    {
        $queue = $this->createStub(Queue::class);

        $exception = new UnableToLeaveQueueException($queue);

        $this->assertSame($queue, $exception->getQueue());
    }
}

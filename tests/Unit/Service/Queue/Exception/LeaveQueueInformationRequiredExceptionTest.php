<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Queue\Exception;

use App\Entity\Queue;
use App\Service\Queue\Exception\LeaveQueueInformationRequiredException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(LeaveQueueInformationRequiredException::class)]
class LeaveQueueInformationRequiredExceptionTest extends KernelTestCase
{
    #[Test]
    public function itShouldReturnPassedQueue(): void
    {
        $exception = new LeaveQueueInformationRequiredException(
            $queue = $this->createStub(Queue::class),
        );

        $this->assertSame($queue, $exception->getQueue());
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Queue\Exception;

use App\Service\Queue\Exception\QueueNotFoundException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(QueueNotFoundException::class)]
class QueueNotFoundExceptionTest extends KernelTestCase
{
    #[Test]
    public function itShouldReturnPassedProperties(): void
    {
        $exception = new QueueNotFoundException(
            $queueName = 'queueName',
            $teamId = 'teamId',
            $userId = 'userId',
        );

        $this->assertSame($queueName, $exception->getQueueName());
        $this->assertSame($teamId, $exception->getTeamId());
        $this->assertSame($userId, $exception->getUserId());
    }
}

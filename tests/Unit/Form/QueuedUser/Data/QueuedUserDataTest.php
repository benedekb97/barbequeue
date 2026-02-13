<?php

declare(strict_types=1);

namespace App\Tests\Unit\Form\QueuedUser\Data;

use App\Entity\Queue;
use App\Entity\User;
use App\Form\QueuedUser\Data\QueuedUserData;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(QueuedUserData::class)]
class QueuedUserDataTest extends KernelTestCase
{
    #[Test]
    public function itShouldReturnPassedParameters(): void
    {
        $data = new QueuedUserData()
            ->setUser($user = $this->createStub(User::class))
            ->setQueueName($queueName = 'queueName')
            ->setQueue($queue = $this->createStub(Queue::class))
            ->setExpiryMinutes($expiry = 1);

        $this->assertSame($expiry, $data->getExpiryMinutes());
        $this->assertSame($queue, $data->getQueue());
        $this->assertSame($user, $data->getUser());
        $this->assertSame($queueName, $data->getQueueName());
    }
}

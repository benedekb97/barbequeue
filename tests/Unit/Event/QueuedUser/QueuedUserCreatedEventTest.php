<?php

declare(strict_types=1);

namespace App\Tests\Unit\Event\QueuedUser;

use App\Entity\QueuedUser;
use App\Event\QueuedUser\QueuedUserCreatedEvent;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(QueuedUserCreatedEvent::class)]
class QueuedUserCreatedEventTest extends KernelTestCase
{
    #[Test]
    public function itShouldReturnSameQueuedUser(): void
    {
        $queuedUser = $this->createStub(QueuedUser::class);

        $event = new QueuedUserCreatedEvent($queuedUser);

        $this->assertSame($queuedUser, $event->getQueuedUser());
    }
}

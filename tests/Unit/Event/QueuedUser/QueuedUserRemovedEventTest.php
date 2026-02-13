<?php

declare(strict_types=1);

namespace App\Tests\Unit\Event\QueuedUser;

use App\Entity\Queue;
use App\Entity\QueuedUser;
use App\Event\QueuedUser\QueuedUserRemovedEvent;
use Faker\Factory;
use Faker\Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(QueuedUserRemovedEvent::class)]
class QueuedUserRemovedEventTest extends KernelTestCase
{
    private Generator $faker;

    public function setUp(): void
    {
        parent::setUp();

        $this->faker = Factory::create();
    }

    #[Test]
    public function itShouldDefaultFalseOnNotificationRequiredAndAutomaticProperties(): void
    {
        $queue = $this->createStub(Queue::class);
        $queuedUser = $this->createStub(QueuedUser::class);

        $event = new QueuedUserRemovedEvent($queuedUser, $queue);

        $this->assertSame($queue, $event->getQueue());
        $this->assertSame($queuedUser, $event->getQueuedUser());
        $this->assertFalse($event->isNotificationRequired());
        $this->assertFalse($event->isAutomatic());
    }

    #[Test]
    public function itShouldReturnSamePropertiesPassed(): void
    {
        $queue = $this->createStub(Queue::class);
        $queuedUser = $this->createStub(QueuedUser::class);

        $event = new QueuedUserRemovedEvent(
            $queuedUser,
            $queue,
            $notificationRequired = $this->faker->boolean(),
            $automatic = $this->faker->boolean(),
        );

        $this->assertSame($queue, $event->getQueue());
        $this->assertSame($queuedUser, $event->getQueuedUser());
        $this->assertEquals($notificationRequired, $event->isNotificationRequired());
        $this->assertEquals($automatic, $event->isAutomatic());
    }
}

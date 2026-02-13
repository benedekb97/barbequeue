<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\Queue;
use App\Entity\QueuedUser;
use App\Entity\User;
use Carbon\CarbonImmutable;
use Faker\Factory;
use Faker\Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(QueuedUser::class)]
class QueuedUserTest extends KernelTestCase
{
    private Generator $faker;

    public function setUp(): void
    {
        parent::setUp();

        $this->faker = Factory::create();
    }

    #[Test]
    public function itShouldSetPropertiesCorrectly(): void
    {
        $queue = $this->createStub(Queue::class);
        $user = $this->createMock(User::class);
        $user->expects($this->once())
            ->method('getSlackId')
            ->willReturn($userId = 'userId');

        $queuedUser = new QueuedUser()
            ->setQueue($queue)
            ->setUser($user)
            ->setExpiresAt(
                $expiresAt = $this->faker->boolean()
                    ? CarbonImmutable::now()
                    : null
            )
            ->setCreatedAtNow()
            ->setUpdatedAtNow();

        $this->assertNotNull($queuedUser->getCreatedAt());
        $this->assertNotNull($queuedUser->getUpdatedAt());

        $queuedUser->setCreatedAt($createdAt = CarbonImmutable::now());
        $queuedUser->setUpdatedAt($updatedAt = CarbonImmutable::now());

        $this->assertSame($createdAt, $queuedUser->getCreatedAt());
        $this->assertSame($updatedAt, $queuedUser->getUpdatedAt());

        $this->assertEquals($expiresAt, $queuedUser->getExpiresAt());
        $this->assertEquals($user, $queuedUser->getUser());
        $this->assertEquals($queue, $queuedUser->getQueue());
        $this->assertEquals('<@'.$userId.'>', $queuedUser->getUserLink());
    }

    #[Test]
    public function itShouldReturnNullIfUserHasNoExpiryMinutesSet(): void
    {
        $queuedUser = new QueuedUser();

        $this->assertNull($queuedUser->getExpiryMinutesLeft());
    }

    #[Test]
    public function itShouldReturnExpiryMinutesIfUserHasNoExpiresAtSet(): void
    {
        $queuedUser = new QueuedUser()
            ->setExpiryMinutes($expiryMinutes = 20);

        $this->assertEquals($expiryMinutes, $queuedUser->getExpiryMinutesLeft());
    }

    #[Test]
    public function itShouldReturnDiffFromNowIfExpiresAtIsSet(): void
    {
        $queuedUser = new QueuedUser()
            ->setExpiryMinutes($expiryMinutes = 20)
            ->setExpiresAt(CarbonImmutable::now()->addMinutes($diff = 10));

        $this->assertNotEquals($expiryMinutes, $queuedUser->getExpiryMinutesLeft());
        $this->assertEquals($diff, $queuedUser->getExpiryMinutesLeft());
    }
}

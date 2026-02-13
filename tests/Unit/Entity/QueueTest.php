<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\Queue;
use App\Entity\QueuedUser;
use App\Entity\User;
use App\Entity\Workspace;
use App\Enum\Queue as QueueType;
use App\Enum\QueueBehaviour;
use Carbon\CarbonImmutable;
use Faker\Factory;
use Faker\Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(Queue::class)]
class QueueTest extends KernelTestCase
{
    private Generator $faker;

    public function setUp(): void
    {
        parent::setUp();

        $this->faker = Factory::create();
    }

    #[Test]
    public function itShouldSetDataCorrectly(): void
    {
        $queue = new Queue();

        $queue->setName($name = $this->faker->firstName())
            ->setWorkspace($workspace = $this->createStub(Workspace::class))
            ->setMaximumEntriesPerUser(
                $maximumEntriesPerUser = $this->faker->boolean()
                    ? $this->faker->numberBetween(1, 10)
                    : null
            )
            ->setExpiryMinutes(
                $expiryMinutes = $this->faker->boolean()
                    ? $this->faker->numberBetween(1, 60)
                    : null
            );

        $queue->setCreatedAtNow();
        $queue->setUpdatedAtNow();

        $this->assertNotNull($queue->getCreatedAt());
        $this->assertNotNull($queue->getUpdatedAt());

        $this->assertEquals(QueueBehaviour::ENFORCE_QUEUE, $queue->getBehaviour());

        $numberOfQueuedUsers = $this->faker->numberBetween(1, 3);

        $queuedUsers = [];

        for ($i = 0; $i < $numberOfQueuedUsers; ++$i) {
            $queuedUser = $this->createMock(QueuedUser::class);
            $queuedUser->expects($this->exactly(2))
                ->method('setQueue')
                ->withAnyParameters()
                ->willReturnSelf();

            $queue->addQueuedUser($queuedUser);
            $queuedUsers[] = $queuedUser;
        }

        foreach ($queue->getQueuedUsers() as $queuedUser) {
            $this->assertContains($queuedUser, $queuedUsers);
        }

        foreach ($queuedUsers as $queuedUser) {
            $queue->removeQueuedUser($queuedUser);

            $this->assertNotContains($queuedUser, $queue->getQueuedUsers());
        }

        $this->assertEquals($name, $queue->getName());
        $this->assertEquals($workspace, $queue->getWorkspace());
        $this->assertEquals($maximumEntriesPerUser, $queue->getMaximumEntriesPerUser());
        $this->assertEquals($expiryMinutes, $queue->getExpiryMinutes());
        $this->assertEquals(QueueType::SIMPLE, $queue->getType());
    }

    #[Test]
    public function itShouldSortQueuedUsersByCreatedAt(): void
    {
        $queue = new Queue();

        $firstQueuedUser = $this->createMock(QueuedUser::class);
        $firstQueuedUser->expects($this->once())
            ->method('setQueue')
            ->with($queue);

        $firstQueuedUser->expects($this->once())
            ->method('getCreatedAt')
            ->willReturn(CarbonImmutable::yesterday());

        $secondQueuedUser = $this->createMock(QueuedUser::class);
        $secondQueuedUser->expects($this->once())
            ->method('setQueue')
            ->with($queue);

        $secondQueuedUser->expects($this->exactly(2))
            ->method('getCreatedAt')
            ->willReturn(CarbonImmutable::today());

        $thirdQueuedUser = $this->createMock(QueuedUser::class);
        $thirdQueuedUser->expects($this->once())
            ->method('setQueue')
            ->with($queue);

        $thirdQueuedUser->expects($this->once())
            ->method('getCreatedAt')
            ->willReturn(CarbonImmutable::tomorrow());

        // Add them in reverse order
        $queue->addQueuedUser($thirdQueuedUser)
            ->addQueuedUser($secondQueuedUser)
            ->addQueuedUser($firstQueuedUser);

        $sortedUsers = $queue->getSortedUsers();

        $this->assertSame($firstQueuedUser, $sortedUsers[0]);
        $this->assertSame($secondQueuedUser, $sortedUsers[1]);
        $this->assertSame($thirdQueuedUser, $sortedUsers[2]);
    }

    #[Test]
    public function itShouldGetLastPlaceInQueueForSpecificUser(): void
    {
        $queue = new Queue();

        $user = $this->createMock(User::class);
        $user->expects($this->exactly(2))
            ->method('getSlackId')
            ->willReturn($userId = 'userId');

        $firstQueuedUser = $this->createMock(QueuedUser::class);
        $firstQueuedUser->expects($this->once())
            ->method('setQueue')
            ->with($queue);

        $firstQueuedUser->expects($this->once())
            ->method('getUser')
            ->willReturn($user);

        $firstQueuedUser->expects($this->once())
            ->method('getCreatedAt')
            ->willReturn(CarbonImmutable::yesterday());

        $secondQueuedUser = $this->createMock(QueuedUser::class);
        $secondQueuedUser->expects($this->once())
            ->method('setQueue')
            ->with($queue);

        $secondQueuedUser->expects($this->once())
            ->method('getUser')
            ->willReturn($user);

        $secondQueuedUser->expects($this->once())
            ->method('getCreatedAt')
            ->willReturn(CarbonImmutable::today());

        $differentUser = $this->createMock(User::class);
        $differentUser->expects($this->once())
            ->method('getSlackId')
            ->willReturn('differentUserId');

        $thirdQueuedUser = $this->createMock(QueuedUser::class);
        $thirdQueuedUser->expects($this->once())
            ->method('setQueue')
            ->with($queue);

        $thirdQueuedUser->expects($this->once())
            ->method('getUser')
            ->willReturn($differentUser);

        $thirdQueuedUser->expects($this->never())
            ->method('getCreatedAt')
            ->willReturn(CarbonImmutable::today());

        // Add them in reverse order
        $queue->addQueuedUser($thirdQueuedUser)
            ->addQueuedUser($secondQueuedUser)
            ->addQueuedUser($firstQueuedUser);

        $this->assertSame($secondQueuedUser, $queue->getLastPlace($userId));
    }

    #[Test]
    public function itShouldReturnNullIfNoQueuedUserHasRequestedUserId(): void
    {
        $queue = new Queue();

        $user = $this->createMock(User::class);
        $user->expects($this->exactly(3))
            ->method('getSlackId')
            ->willReturn($userId = 'userId');

        $firstQueuedUser = $this->createMock(QueuedUser::class);
        $firstQueuedUser->expects($this->once())
            ->method('setQueue')
            ->with($queue);

        $firstQueuedUser->expects($this->once())
            ->method('getUser')
            ->willReturn($user);

        $firstQueuedUser->expects($this->never())
            ->method('getCreatedAt')
            ->willReturn(CarbonImmutable::yesterday());

        $secondQueuedUser = $this->createMock(QueuedUser::class);
        $secondQueuedUser->expects($this->once())
            ->method('setQueue')
            ->with($queue);

        $secondQueuedUser->expects($this->once())
            ->method('getUser')
            ->willReturn($user);

        $secondQueuedUser->expects($this->never())
            ->method('getCreatedAt')
            ->willReturn(CarbonImmutable::today());

        $thirdQueuedUser = $this->createMock(QueuedUser::class);
        $thirdQueuedUser->expects($this->once())
            ->method('setQueue')
            ->with($queue);

        $thirdQueuedUser->expects($this->once())
            ->method('getUser')
            ->willReturn($user);

        $thirdQueuedUser->expects($this->never())
            ->method('getCreatedAt')
            ->willReturn(CarbonImmutable::today());

        // Add them in reverse order
        $queue->addQueuedUser($thirdQueuedUser)
            ->addQueuedUser($secondQueuedUser)
            ->addQueuedUser($firstQueuedUser);

        $this->assertNull($queue->getLastPlace('differentUserId'));
    }

    #[Test]
    public function itShouldReturnNullOnGetLastPlaceIfQueueEmpty(): void
    {
        $queue = new Queue();

        $this->assertNull($queue->getLastPlace('userId'));
    }

    #[Test]
    public function itShouldReturnFirstPlaceInQueue(): void
    {
        $queue = new Queue();

        $firstQueuedUser = $this->createMock(QueuedUser::class);
        $firstQueuedUser->expects($this->once())
            ->method('setQueue')
            ->with($queue);

        $firstQueuedUser->expects($this->once())
            ->method('getCreatedAt')
            ->willReturn(CarbonImmutable::yesterday());

        $secondQueuedUser = $this->createMock(QueuedUser::class);
        $secondQueuedUser->expects($this->once())
            ->method('setQueue')
            ->with($queue);

        $secondQueuedUser->expects($this->exactly(2))
            ->method('getCreatedAt')
            ->willReturn(CarbonImmutable::today());

        $thirdQueuedUser = $this->createMock(QueuedUser::class);
        $thirdQueuedUser->expects($this->once())
            ->method('setQueue')
            ->with($queue);

        $thirdQueuedUser->expects($this->once())
            ->method('getCreatedAt')
            ->willReturn(CarbonImmutable::tomorrow());

        // Add them in reverse order
        $queue->addQueuedUser($thirdQueuedUser)
            ->addQueuedUser($secondQueuedUser)
            ->addQueuedUser($firstQueuedUser);

        $this->assertSame($firstQueuedUser, $queue->getFirstPlace());
    }

    #[Test]
    public function itShouldReturnNullOnGetFirstPlaceIfQueueEmpty(): void
    {
        $queue = new Queue();

        $this->assertNull($queue->getFirstPlace());
    }

    #[Test]
    public function itShouldOnlyReturnQueuedUsersWithCorrectUserId(): void
    {
        $queue = new Queue();

        $userId = 'userId';

        $expectedUsers = [];

        $user = $this->createMock(User::class);
        $user->expects($this->exactly(5))
            ->method('getSlackId')
            ->willReturn($userId);

        for ($i = 0; $i < $expectedCount = 5; ++$i) {
            $queuedUser = $this->createMock(QueuedUser::class);
            $queuedUser->expects($this->once())
                ->method('setQueue')
                ->with($queue)
                ->willReturnSelf();

            $queuedUser->expects($this->once())
                ->method('getUser')
                ->willReturn($user);

            $queue->addQueuedUser($queuedUser);

            $expectedUsers[] = $queuedUser;
        }

        $secondUser = $this->createMock(User::class);
        $secondUser->expects($this->exactly(3))
            ->method('getSlackId')
            ->willReturn('differentUserId');

        for ($i = 0; $i < 3; ++$i) {
            $queuedUser = $this->createMock(QueuedUser::class);
            $queuedUser->expects($this->once())
                ->method('setQueue')
                ->with($queue)
                ->willReturnSelf();

            $queuedUser->expects($this->once())
                ->method('getUser')
                ->willReturn($secondUser);

            $queue->addQueuedUser($queuedUser);
        }

        $result = $queue->getQueuedUsersByUserId($userId);

        $this->assertCount($expectedCount, $result);

        foreach ($expectedUsers as $expectedUser) {
            $this->assertContains($expectedUser, $result);
        }

        foreach ($result as $queuedUser) {
            $this->assertContains($queuedUser, $expectedUsers);
        }
    }

    #[Test]
    public function itShouldReturnTrueOnCanLeaveIfQueueHasEntryWithUserId(): void
    {
        $queue = new Queue();

        $queuedUser = $this->createMock(QueuedUser::class);
        $queuedUser->expects($this->once())
            ->method('setQueue')
            ->with($queue)
            ->willReturnSelf();

        $user = $this->createMock(User::class);
        $user->expects($this->once())
            ->method('getSlackId')
            ->willReturn($userId = 'userId');

        $queuedUser->expects($this->once())
            ->method('getUser')
            ->willReturn($user);

        $queue->addQueuedUser($queuedUser);

        $this->assertTrue($queue->canLeave($userId));
    }

    #[Test]
    public function itShouldReturnTrueOnCanJoinIfNoMaximumEntriesSetOnQueue(): void
    {
        $queue = new Queue()
            ->setMaximumEntriesPerUser(null);

        $this->assertTrue($queue->canJoin('anyUserId'));
    }

    #[Test]
    public function itShouldReturnTrueOnCanJoinIfUserHasNoEntriesWithMaximumEntriesSetOnQueue(): void
    {
        $queue = new Queue()
            ->setMaximumEntriesPerUser(1);

        $this->assertTrue($queue->canJoin('anyUserId'));
    }

    #[Test]
    public function itShouldReturnTrueOnCanJoinIfUserHasOneLessThanMaximumEntriesSetOnQueue(): void
    {
        $queue = new Queue()
            ->setMaximumEntriesPerUser($maximumEntries = $this->faker->numberBetween(3, 5));

        $user = $this->createMock(User::class);
        $user->expects($this->exactly($maximumEntries - 1))
            ->method('getSlackId')
            ->willReturn($userId = 'userId');

        for ($i = 0; $i < $maximumEntries - 1; ++$i) {
            $queuedUser = $this->createMock(QueuedUser::class);
            $queuedUser->expects($this->once())
                ->method('setQueue')
                ->with($queue)
                ->willReturnSelf();

            $queuedUser->expects($this->once())
                ->method('getUser')
                ->willReturn($user);

            $queue->addQueuedUser($queuedUser);
        }

        $this->assertTrue($queue->canJoin($userId));
    }

    #[Test]
    public function itShouldReturnFalseOnCanJoinIfUserHasExactlyMaximumEntriesSetOnQueue(): void
    {
        $queue = new Queue()
            ->setMaximumEntriesPerUser($maximumEntries = $this->faker->numberBetween(3, 5));

        $user = $this->createMock(User::class);
        $user->expects($this->exactly($maximumEntries))
            ->method('getSlackId')
            ->willReturn($userId = 'userId');

        for ($i = 0; $i < $maximumEntries; ++$i) {
            $queuedUser = $this->createMock(QueuedUser::class);
            $queuedUser->expects($this->once())
                ->method('setQueue')
                ->with($queue)
                ->willReturnSelf();

            $queuedUser->expects($this->once())
                ->method('getUser')
                ->willReturn($user);

            $queue->addQueuedUser($queuedUser);
        }

        $this->assertFalse($queue->canJoin($userId));
    }

    #[Test]
    public function itShouldReturnFalseOnCanJoinIfUserHasMoreThanMaximumEntriesSetOnQueue(): void
    {
        $queue = new Queue()
            ->setMaximumEntriesPerUser($maximumEntries = $this->faker->numberBetween(3, 5));

        $user = $this->createMock(User::class);
        $user->expects($this->exactly($maximumEntries + 1))
            ->method('getSlackId')
            ->willReturn($userId = 'userId');

        for ($i = 0; $i < $maximumEntries + 1; ++$i) {
            $queuedUser = $this->createMock(QueuedUser::class);
            $queuedUser->expects($this->once())
                ->method('setQueue')
                ->with($queue)
                ->willReturnSelf();

            $queuedUser->expects($this->once())
                ->method('getUser')
                ->willReturn($user);

            $queue->addQueuedUser($queuedUser);
        }

        $this->assertFalse($queue->canJoin($userId));
    }

    #[Test]
    public function itShouldReturnTrueIfOneMemberOfQueueHasExpiryMinutesSet(): void
    {
        $queue = new Queue();

        $firstUser = $this->createMock(QueuedUser::class);
        $firstUser->expects($this->once())
            ->method('getExpiryMinutes')
            ->willReturn(null);

        $firstUser->expects($this->once())
            ->method('setQueue')
            ->with($queue)
            ->willReturnSelf();

        $secondUser = $this->createMock(QueuedUser::class);
        $secondUser->expects($this->once())
            ->method('getExpiryMinutes')
            ->willReturn(null);

        $secondUser->expects($this->once())
            ->method('setQueue')
            ->with($queue)
            ->willReturnSelf();

        $thirdUser = $this->createMock(QueuedUser::class);
        $thirdUser->expects($this->once())
            ->method('getExpiryMinutes')
            ->willReturn($expiry = 10);

        $thirdUser->expects($this->once())
            ->method('setQueue')
            ->with($queue)
            ->willReturnSelf();

        $queue->addQueuedUser($firstUser)
            ->addQueuedUser($secondUser)
            ->addQueuedUser($thirdUser);

        $this->assertTrue($queue->hasQueuedUserWithExpiryMinutes());
    }

    #[Test]
    public function itShouldReturnTrueIfAllMembersOfQueueHaveExpiryMinutesSet(): void
    {
        $queue = new Queue();

        $firstUser = $this->createMock(QueuedUser::class);
        $firstUser->expects($this->once())
            ->method('getExpiryMinutes')
            ->willReturn(15);

        $firstUser->expects($this->once())
            ->method('setQueue')
            ->with($queue)
            ->willReturnSelf();

        $secondUser = $this->createMock(QueuedUser::class);
        $secondUser->expects($this->never())
            ->method('getExpiryMinutes')
            ->willReturn(20);

        $secondUser->expects($this->once())
            ->method('setQueue')
            ->with($queue)
            ->willReturnSelf();

        $thirdUser = $this->createMock(QueuedUser::class);
        $thirdUser->expects($this->never())
            ->method('getExpiryMinutes')
            ->willReturn(10);

        $thirdUser->expects($this->once())
            ->method('setQueue')
            ->with($queue)
            ->willReturnSelf();

        $queue->addQueuedUser($firstUser)
            ->addQueuedUser($secondUser)
            ->addQueuedUser($thirdUser);

        $this->assertTrue($queue->hasQueuedUserWithExpiryMinutes());
    }

    #[Test]
    public function itShouldReturnTrueIfNoMembersOfQueueHaveExpiryMinutesSet(): void
    {
        $queue = new Queue();

        $firstUser = $this->createMock(QueuedUser::class);
        $firstUser->expects($this->once())
            ->method('getExpiryMinutes')
            ->willReturn(null);

        $firstUser->expects($this->once())
            ->method('setQueue')
            ->with($queue)
            ->willReturnSelf();

        $secondUser = $this->createMock(QueuedUser::class);
        $secondUser->expects($this->once())
            ->method('getExpiryMinutes')
            ->willReturn(null);

        $secondUser->expects($this->once())
            ->method('setQueue')
            ->with($queue)
            ->willReturnSelf();

        $thirdUser = $this->createMock(QueuedUser::class);
        $thirdUser->expects($this->once())
            ->method('getExpiryMinutes')
            ->willReturn(null);

        $thirdUser->expects($this->once())
            ->method('setQueue')
            ->with($queue)
            ->willReturnSelf();

        $queue->addQueuedUser($firstUser)
            ->addQueuedUser($secondUser)
            ->addQueuedUser($thirdUser);

        $this->assertFalse($queue->hasQueuedUserWithExpiryMinutes());
    }

    #[Test]
    public function itShouldReturnFalseOnCanReleaseIfQueueIsEmpty(): void
    {
        $queue = new Queue();

        $this->assertFalse($queue->canRelease('userId'));
    }

    #[Test]
    public function itShouldReturnFalseOnCanReleaseIfFirstPersonHasDifferentUserId(): void
    {
        $queue = new Queue();

        $user = $this->createMock(User::class);
        $user->expects($this->once())
            ->method('getSlackId')
            ->willReturn('differentUserId');

        $queuedUser = $this->createMock(QueuedUser::class);
        $queuedUser->expects($this->once())
            ->method('getUser')
            ->willReturn($user);

        $queuedUser->expects($this->once())
            ->method('setQueue')
            ->willReturnSelf();

        $queue->addQueuedUser($queuedUser);

        $this->assertFalse($queue->canRelease('userId'));
    }

    #[Test]
    public function itShouldReturnTrueOnCanReleaseIfFirstPersonHasSameUserId(): void
    {
        $queue = new Queue();

        $user = $this->createMock(User::class);
        $user->expects($this->once())
            ->method('getSlackId')
            ->willReturn($userId = 'userId');

        $queuedUser = $this->createMock(QueuedUser::class);
        $queuedUser->expects($this->once())
            ->method('getUser')
            ->willReturn($user);

        $queuedUser->expects($this->once())
            ->method('setQueue')
            ->willReturnSelf();

        $queue->addQueuedUser($queuedUser);

        $this->assertTrue($queue->canRelease($userId));
    }

    #[Test]
    public function itShouldReturnFirstPlacementIfOnlyOnePlacement(): void
    {
        $user = $this->createMock(User::class);
        $user->expects($this->once())
            ->method('getSlackId')
            ->willReturn($userId = 'userId');

        $queuedUser = $this->createMock(QueuedUser::class);
        $queuedUser->expects($this->once())
            ->method('getUser')
            ->willReturn($user);

        $queue = new Queue()
            ->addQueuedUser($queuedUser);

        $placements = $queue->getPlacementString($userId);

        $this->assertEquals('1st', $placements);
    }
}

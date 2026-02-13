<?php

declare(strict_types=1);

namespace App\Tests\Unit\EventSubscriber;

use App\Calculator\ClosestFiveMinutesCalculator;
use App\Entity\DeploymentQueue;
use App\Entity\Queue;
use App\Entity\QueuedUser;
use App\Entity\User;
use App\Event\QueuedUser\QueuedUserCreatedEvent;
use App\Event\QueuedUser\QueuedUserRemovedEvent;
use App\EventSubscriber\QueuedUserEventSubscriber;
use App\Slack\Response\PrivateMessage\Factory\FirstInQueueMessageFactory;
use App\Slack\Response\PrivateMessage\Factory\RemovedFromQueueMessageFactory;
use App\Slack\Response\PrivateMessage\PrivateMessageHandler;
use App\Slack\Response\PrivateMessage\SlackPrivateMessage;
use App\Tests\Unit\LoggerAwareTestCase;
use Carbon\CarbonImmutable;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Faker\Factory;
use Faker\Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(QueuedUserEventSubscriber::class)]
class QueuedUserEventSubscriberTest extends LoggerAwareTestCase
{
    private Generator $faker;

    public function setUp(): void
    {
        parent::setUp();

        $this->faker = Factory::create();
    }

    #[Test]
    public function itShouldSubscribeToCorrectEvents(): void
    {
        $subscribedEvents = QueuedUserEventSubscriber::getSubscribedEvents();

        $this->assertArrayHasKey(QueuedUserCreatedEvent::class, $subscribedEvents);
        $this->assertEquals('handleCreated', $subscribedEvents[QueuedUserCreatedEvent::class]);

        $this->assertArrayHasKey(QueuedUserRemovedEvent::class, $subscribedEvents);
        $this->assertEquals('handleRemoved', $subscribedEvents[QueuedUserRemovedEvent::class]);
    }

    #[Test]
    public function itShouldReturnIfQueuedUserHasNoQueueSetOnHandleCreated(): void
    {
        $queuedUser = $this->createMock(QueuedUser::class);
        $queuedUser->expects($this->once())
            ->method('getQueue')
            ->willReturn(null);

        $queuedUser->expects($this->once())
            ->method('getId')
            ->willReturn($queuedUserId = 1);

        $this->expectsError('Queued user {queuedUser} does not have a queue set.', [
            'queuedUser' => $queuedUserId,
        ]);

        $event = $this->createMock(QueuedUserCreatedEvent::class);
        $event->expects($this->once())
            ->method('getQueuedUser')
            ->willReturn($queuedUser);

        $subscriber = new QueuedUserEventSubscriber(
            $this->createStub(EntityManagerInterface::class),
            $this->getLogger(),
            $this->createStub(PrivateMessageHandler::class),
            $this->createStub(RemovedFromQueueMessageFactory::class),
            $this->createStub(FirstInQueueMessageFactory::class),
            $this->createStub(ClosestFiveMinutesCalculator::class),
        );

        $subscriber->handleCreated($event);
    }

    #[Test]
    public function itShouldReturnIfQueueIsDeploymentQueueOnHandleCreated(): void
    {
        $queuedUser = $this->createMock(QueuedUser::class);
        $queuedUser->expects($this->once())
            ->method('getQueue')
            ->willReturn($this->createStub(DeploymentQueue::class));

        $event = $this->createMock(QueuedUserCreatedEvent::class);
        $event->expects($this->once())
            ->method('getQueuedUser')
            ->willReturn($queuedUser);

        $subscriber = new QueuedUserEventSubscriber(
            $this->createStub(EntityManagerInterface::class),
            $this->getLogger(),
            $this->createStub(PrivateMessageHandler::class),
            $this->createStub(RemovedFromQueueMessageFactory::class),
            $this->createStub(FirstInQueueMessageFactory::class),
            $this->createStub(ClosestFiveMinutesCalculator::class),
        );

        $subscriber->handleCreated($event);
    }

    #[Test]
    public function itShouldReturnIfQueuedUserHasNoExpirySetOnHandleCreated(): void
    {
        $this->expectsDebug('Handling created event for queued user: {queue}', [
            'queue' => $queueId = 1,
        ]);

        $queue = $this->createMock(Queue::class);

        $queue->expects($this->once())
            ->method('getId')
            ->willReturn($queueId);

        $queuedUser = $this->createMock(QueuedUser::class);
        $queuedUser->expects($this->once())
            ->method('getQueue')
            ->willReturn($queue);

        $queuedUser->expects($this->once())
            ->method('getExpiryMinutes')
            ->willReturn(null);

        $event = $this->createMock(QueuedUserCreatedEvent::class);
        $event->expects($this->once())
            ->method('getQueuedUser')
            ->willReturn($queuedUser);

        $subscriber = new QueuedUserEventSubscriber(
            $this->createStub(EntityManagerInterface::class),
            $this->getLogger(),
            $this->createStub(PrivateMessageHandler::class),
            $this->createStub(RemovedFromQueueMessageFactory::class),
            $this->createStub(FirstInQueueMessageFactory::class),
            $this->createStub(ClosestFiveMinutesCalculator::class),
        );

        $subscriber->handleCreated($event);
    }

    #[Test]
    public function itShouldSetExpiryOnUserIfFirstUserInQueue(): void
    {
        $collection = $this->createMock(Collection::class);
        $collection->expects($this->exactly(2))
            ->method('count')
            ->willReturn($collectionCount = 1);

        $queue = $this->createMock(Queue::class);
        $queue->expects($this->exactly(2))
            ->method('getQueuedUsers')
            ->willReturn($collection);

        $queue->expects($this->once())
            ->method('getId')
            ->willReturn($queueId = 1);

        $queuedUser = $this->createMock(QueuedUser::class);
        $queuedUser->expects($this->once())
            ->method('getQueue')
            ->willReturn($queue);

        $queuedUser->expects($this->once())
            ->method('getExpiryMinutes')
            ->willReturn($expiry = 15);

        $expectedTime = CarbonImmutable::now()->addMinutes($expiry);

        $calculator = $this->createMock(ClosestFiveMinutesCalculator::class);
        $calculator->expects($this->once())
            ->method('calculate')
            ->willReturnCallback(function ($argument) use ($expectedTime) {
                return $expectedTime;
            });

        $queuedUser->expects($this->once())
            ->method('setExpiresAt')
            ->willReturnCallback(function ($argument) use ($queuedUser, $expectedTime) {
                $this->assertInstanceOf(CarbonImmutable::class, $argument);

                $this->assertTrue($expectedTime->equalTo($argument));

                $this->expectsInfo('User is in first place, setting expiry time to {expiresAt}.', [
                    'expiresAt' => $argument->toIso8601ZuluString(),
                ]);

                return $queuedUser;
            });

        $event = $this->createMock(QueuedUserCreatedEvent::class);
        $event->expects($this->once())
            ->method('getQueuedUser')
            ->willReturn($queuedUser);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->once())
            ->method('persist')
            ->with($queuedUser);

        $this
            ->expectsDebug('Handling created event for queued user: {queue}', [
                'queue' => $queueId,
            ])
            ->expectsDebug('{count} users in the queue.', [
                'count' => $collectionCount,
            ]);

        $subscriber = new QueuedUserEventSubscriber(
            $entityManager,
            $this->getLogger(),
            $this->createStub(PrivateMessageHandler::class),
            $this->createStub(RemovedFromQueueMessageFactory::class),
            $this->createStub(FirstInQueueMessageFactory::class),
            $calculator,
        );

        $subscriber->handleCreated($event);
    }

    #[Test]
    public function itShouldReturnEarlyIfQueueIsDeploymentQueueOnHandleRemoved(): void
    {
        $queue = $this->createMock(DeploymentQueue::class);
        $queue->expects($this->once())
            ->method('getId')
            ->willReturn($queueId = 1);

        $event = $this->createMock(QueuedUserRemovedEvent::class);
        $event->expects($this->once())
            ->method('getQueue')
            ->willReturn($queue);

        $this->expectsDebug('Handling removed event for queued user: {queue}', [
            'queue' => $queueId,
        ]);

        $eventSubscriber = new QueuedUserEventSubscriber(
            $this->createStub(EntityManagerInterface::class),
            $this->getLogger(),
            $this->createStub(PrivateMessageHandler::class),
            $this->createStub(RemovedFromQueueMessageFactory::class),
            $this->createStub(FirstInQueueMessageFactory::class),
            $this->createStub(ClosestFiveMinutesCalculator::class),
        );

        $eventSubscriber->handleRemoved($event);
    }

    #[Test]
    public function itShouldNotCallResponseHandlerOrMessageFactoryIfNotificationNotRequiredOnHandleRemoved(): void
    {
        $queuedUser = $this->createStub(QueuedUser::class);

        $collection = $this->createMock(Collection::class);
        $collection->expects($this->once())
            ->method('count')
            ->willReturn(0);

        $queue = $this->createMock(Queue::class);
        $queue->expects($this->once())
            ->method('removeQueuedUser')
            ->with($queuedUser)
            ->willReturnSelf();

        $queue->expects($this->once())
            ->method('getQueuedUsers')
            ->willReturn($collection);

        $queue->expects($this->exactly(2))
            ->method('getId')
            ->willReturn($queueId = 1);

        $event = $this->createMock(QueuedUserRemovedEvent::class);
        $event->expects($this->once())
            ->method('isNotificationRequired')
            ->willReturn(false);

        $event->expects($this->once())
            ->method('getQueuedUser')
            ->willReturn($queuedUser);

        $event->expects($this->once())
            ->method('getQueue')
            ->willReturn($queue);

        $responseHandler = $this->createMock(PrivateMessageHandler::class);
        $responseHandler->expects($this->never())
            ->method('handle')
            ->withAnyParameters();

        $messageFactory = $this->createMock(RemovedFromQueueMessageFactory::class);
        $messageFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $this
            ->expectsDebug('Handling removed event for queued user: {queue}', [
                'queue' => $queueId,
            ])
            ->expectsInfo('Queue {queue} does not have any queued users.', [
                'queue' => $queueId,
            ]);

        $subscriber = new QueuedUserEventSubscriber(
            $this->createStub(EntityManagerInterface::class),
            $this->getLogger(),
            $responseHandler,
            $messageFactory,
            $this->createStub(FirstInQueueMessageFactory::class),
            $this->createStub(ClosestFiveMinutesCalculator::class),
        );

        $subscriber->handleRemoved($event);
    }

    #[Test]
    public function itShouldCallResponseHandlerAndMessageFactoryIfNotificationRequiredOnHandleRemoved(): void
    {
        $user = $this->createMock(User::class);
        $user->expects($this->once())
            ->method('getSlackId')
            ->willReturn($userId = 'userId');

        $queuedUser = $this->createMock(QueuedUser::class);
        $queuedUser->expects($this->once())
            ->method('getUser')
            ->willReturn($user);

        $collection = $this->createMock(Collection::class);
        $collection->expects($this->once())
            ->method('count')
            ->willReturn(0);

        $queue = $this->createMock(Queue::class);
        $queue->expects($this->once())
            ->method('removeQueuedUser')
            ->with($queuedUser)
            ->willReturnSelf();

        $queue->expects($this->once())
            ->method('getQueuedUsers')
            ->willReturn($collection);

        $queue->expects($this->exactly(2))
            ->method('getId')
            ->willReturn($queueId = 1);

        $event = $this->createMock(QueuedUserRemovedEvent::class);
        $event->expects($this->once())
            ->method('isNotificationRequired')
            ->willReturn(true);

        $event->expects($this->once())
            ->method('getQueuedUser')
            ->willReturn($queuedUser);

        $event->expects($this->once())
            ->method('getQueue')
            ->willReturn($queue);

        $event->expects($this->once())
            ->method('isAutomatic')
            ->willReturn($automatic = $this->faker->boolean());

        $response = $this->createStub(SlackPrivateMessage::class);

        $messageFactory = $this->createMock(RemovedFromQueueMessageFactory::class);
        $messageFactory->expects($this->once())
            ->method('create')
            ->with($queuedUser, $queue, $automatic)
            ->willReturn($response);

        $responseHandler = $this->createMock(PrivateMessageHandler::class);
        $responseHandler->expects($this->once())
            ->method('handle')
            ->with($response);

        $this
            ->expectsDebug('Handling removed event for queued user: {queue}', [
                'queue' => $queueId,
            ])
            ->expectsInfo('Sending notification to {userId}', [
                'userId' => $userId,
            ])
            ->expectsInfo('Queue {queue} does not have any queued users.', [
                'queue' => $queueId,
            ]);

        $subscriber = new QueuedUserEventSubscriber(
            $this->createStub(EntityManagerInterface::class),
            $this->getLogger(),
            $responseHandler,
            $messageFactory,
            $this->createStub(FirstInQueueMessageFactory::class),
            $this->createStub(ClosestFiveMinutesCalculator::class),
        );

        $subscriber->handleRemoved($event);
    }

    #[Test]
    public function itShouldReturnEarlyIfQueuedUserCountIsZero(): void
    {
        $user = $this->createMock(User::class);
        $user->expects($this->once())
            ->method('getSlackId')
            ->willReturn($userId = 'userId');

        $queuedUser = $this->createMock(QueuedUser::class);
        $queuedUser->expects($this->once())
            ->method('getUser')
            ->willReturn($user);

        $collection = $this->createMock(Collection::class);
        $collection->expects($this->once())
            ->method('count')
            ->willReturn(0);

        $queue = $this->createMock(Queue::class);
        $queue->expects($this->once())
            ->method('removeQueuedUser')
            ->with($queuedUser)
            ->willReturnSelf();

        $queue->expects($this->once())
            ->method('getQueuedUsers')
            ->willReturn($collection);

        $queue->expects($this->exactly(2))
            ->method('getId')
            ->willReturn($queueId = 1);

        $event = $this->createMock(QueuedUserRemovedEvent::class);
        $event->expects($this->once())
            ->method('isNotificationRequired')
            ->willReturn(true);

        $event->expects($this->once())
            ->method('getQueuedUser')
            ->willReturn($queuedUser);

        $event->expects($this->once())
            ->method('getQueue')
            ->willReturn($queue);

        $event->expects($this->once())
            ->method('isAutomatic')
            ->willReturn($automatic = $this->faker->boolean());

        $response = $this->createStub(SlackPrivateMessage::class);

        $messageFactory = $this->createMock(RemovedFromQueueMessageFactory::class);
        $messageFactory->expects($this->once())
            ->method('create')
            ->with($queuedUser, $queue, $automatic)
            ->willReturn($response);

        $responseHandler = $this->createMock(PrivateMessageHandler::class);
        $responseHandler->expects($this->once())
            ->method('handle')
            ->with($response);

        $this
            ->expectsDebug('Handling removed event for queued user: {queue}', [
                'queue' => $queueId,
            ])
            ->expectsInfo('Sending notification to {userId}', [
                'userId' => $userId,
            ])
            ->expectsInfo('Queue {queue} does not have any queued users.', [
                'queue' => $queueId,
            ]);

        $subscriber = new QueuedUserEventSubscriber(
            $this->createStub(EntityManagerInterface::class),
            $this->getLogger(),
            $responseHandler,
            $messageFactory,
            $this->createStub(FirstInQueueMessageFactory::class),
            $this->createStub(ClosestFiveMinutesCalculator::class),
        );

        $subscriber->handleRemoved($event);
    }

    /**
     * This test case is not an actual occurrence that can happen, as getQueuedUsers()->count() will be zero if there
     * are still users in the queue, however since there is logic in the code to please phpstan I have added a test case
     * for it.
     */
    #[Test]
    public function itShouldNotPersistNextUserIfNotExistsOnHandleRemoved(): void
    {
        $user = $this->createMock(User::class);
        $user->expects($this->once())
            ->method('getSlackId')
            ->willReturn($userId = 'userId');

        $queuedUser = $this->createMock(QueuedUser::class);
        $queuedUser->expects($this->once())
            ->method('getUser')
            ->willReturn($user);

        $collection = $this->createMock(Collection::class);
        $collection->expects($this->once())
            ->method('count')
            ->willReturn(1);

        $queue = $this->createMock(Queue::class);
        $queue->expects($this->once())
            ->method('removeQueuedUser')
            ->with($queuedUser)
            ->willReturnSelf();

        $queue->expects($this->once())
            ->method('getQueuedUsers')
            ->willReturn($collection);

        $queue->expects($this->once())
            ->method('getFirstPlace')
            ->willReturn(null);

        $queue->expects($this->exactly(2))
            ->method('getId')
            ->willReturn($queueId = 1);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->never())
            ->method('persist')
            ->withAnyParameters();

        $event = $this->createMock(QueuedUserRemovedEvent::class);
        $event->expects($this->once())
            ->method('isNotificationRequired')
            ->willReturn(true);

        $event->expects($this->once())
            ->method('getQueuedUser')
            ->willReturn($queuedUser);

        $event->expects($this->once())
            ->method('getQueue')
            ->willReturn($queue);

        $event->expects($this->once())
            ->method('isAutomatic')
            ->willReturn($automatic = $this->faker->boolean());

        $response = $this->createStub(SlackPrivateMessage::class);

        $messageFactory = $this->createMock(RemovedFromQueueMessageFactory::class);
        $messageFactory->expects($this->once())
            ->method('create')
            ->with($queuedUser, $queue, $automatic)
            ->willReturn($response);

        $responseHandler = $this->createMock(PrivateMessageHandler::class);
        $responseHandler->expects($this->once())
            ->method('handle')
            ->with($response);

        $firstInQueueMessageFactory = $this->createMock(FirstInQueueMessageFactory::class);
        $firstInQueueMessageFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $this
            ->expectsDebug('Handling removed event for queued user: {queue}', [
                'queue' => $queueId,
            ])
            ->expectsInfo('Sending notification to {userId}', [
                'userId' => $userId,
            ])
            ->expectsWarning('Queue {queue} returned non-zero count on its queued users, but could not resolve next user.', [
                'queue' => $queueId,
            ]);

        $subscriber = new QueuedUserEventSubscriber(
            $entityManager,
            $this->getLogger(),
            $responseHandler,
            $messageFactory,
            $firstInQueueMessageFactory,
            $this->createStub(ClosestFiveMinutesCalculator::class),
        );

        $subscriber->handleRemoved($event);
    }

    #[Test]
    public function itShouldNotUpdateNextUserIfHasNoExpiryMinutesSet(): void
    {
        $user = $this->createMock(User::class);
        $user->expects($this->once())
            ->method('getSlackId')
            ->willReturn($userId = 'userId');

        $queuedUser = $this->createMock(QueuedUser::class);
        $queuedUser->expects($this->once())
            ->method('getUser')
            ->willReturn($user);

        $collection = $this->createMock(Collection::class);
        $collection->expects($this->once())
            ->method('count')
            ->willReturn(1);

        $nextUser = $this->createMock(QueuedUser::class);
        $nextUser->expects($this->once())
            ->method('getExpiryMinutes')
            ->willReturn(null);

        $queue = $this->createMock(Queue::class);
        $queue->expects($this->once())
            ->method('removeQueuedUser')
            ->with($queuedUser)
            ->willReturnSelf();

        $queue->expects($this->once())
            ->method('getQueuedUsers')
            ->willReturn($collection);

        $queue->expects($this->once())
            ->method('getFirstPlace')
            ->willReturn($nextUser);

        $queue->expects($this->exactly(2))
            ->method('getId')
            ->willReturn($queueId = 1);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->never())
            ->method('persist')
            ->withAnyParameters();

        $event = $this->createMock(QueuedUserRemovedEvent::class);
        $event->expects($this->once())
            ->method('isNotificationRequired')
            ->willReturn(true);

        $event->expects($this->once())
            ->method('getQueuedUser')
            ->willReturn($queuedUser);

        $event->expects($this->once())
            ->method('getQueue')
            ->willReturn($queue);

        $event->expects($this->once())
            ->method('isAutomatic')
            ->willReturn($automatic = $this->faker->boolean());

        $response = $this->createStub(SlackPrivateMessage::class);

        $messageFactory = $this->createMock(RemovedFromQueueMessageFactory::class);
        $messageFactory->expects($this->once())
            ->method('create')
            ->with($queuedUser, $queue, $automatic)
            ->willReturn($response);

        $responseHandler = $this->createMock(PrivateMessageHandler::class);
        $responseHandler->expects($this->once())
            ->method('handle')
            ->with($response);

        $firstInQueueMessageFactory = $this->createMock(FirstInQueueMessageFactory::class);
        $firstInQueueMessageFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $this
            ->expectsDebug('Handling removed event for queued user: {queue}', [
                'queue' => $queueId,
            ])
            ->expectsInfo('Sending notification to {userId}', [
                'userId' => $userId,
            ])
            ->expectsInfo('Next queued user on queue {queue} has no expiry set.', [
                'queue' => $queueId,
            ]);

        $subscriber = new QueuedUserEventSubscriber(
            $entityManager,
            $this->getLogger(),
            $responseHandler,
            $messageFactory,
            $firstInQueueMessageFactory,
            $this->createStub(ClosestFiveMinutesCalculator::class),
        );

        $subscriber->handleRemoved($event);
    }

    #[Test]
    public function itShouldNotSendFirstInQueueMessageIfNextUserIsSameAsCurrentUser(): void
    {
        $user = $this->createMock(User::class);
        $user->expects($this->once())
            ->method('getSlackId')
            ->willReturn($userId = 'userId');

        $queuedUser = $this->createMock(QueuedUser::class);
        $queuedUser->expects($this->exactly(2))
            ->method('getUser')
            ->willReturn($user);

        $collection = $this->createMock(Collection::class);
        $collection->expects($this->once())
            ->method('count')
            ->willReturn(1);

        $queue = $this->createMock(Queue::class);
        $queue->expects($this->once())
            ->method('removeQueuedUser')
            ->with($queuedUser)
            ->willReturnSelf();

        $queue->expects($this->once())
            ->method('getQueuedUsers')
            ->willReturn($collection);

        $queue->expects($this->exactly(3))
            ->method('getId')
            ->willReturn($queueId = 1);

        $nextUser = $this->createMock(QueuedUser::class);
        $nextUser->expects($this->once())
            ->method('getExpiryMinutes')
            ->willReturn($expiry = 15);

        $expectedExpiry = CarbonImmutable::now()->addMinutes($expiry);

        $calculator = $this->createMock(ClosestFiveMinutesCalculator::class);
        $calculator->expects($this->once())
            ->method('calculate')
            ->willReturnCallback(function ($argument) use ($expectedExpiry) {
                $this->assertInstanceOf(CarbonImmutable::class, $argument);

                return $expectedExpiry;
            });

        $nextUser->expects($this->once())
            ->method('setExpiresAt')
            ->willReturnCallback(function ($argument) use ($nextUser, $queueId, $expectedExpiry) {
                $this->assertInstanceOf(CarbonImmutable::class, $argument);

                $this->assertTrue($expectedExpiry->equalTo($argument));

                $this->expectsInfo('Set expiry on next user in queue {queue} to {expiresAt}.', [
                    'queue' => $queueId,
                    'expiresAt' => $argument->toIso8601ZuluString(),
                ]);

                return $nextUser;
            });

        $nextUser->expects($this->once())
            ->method('getUser')
            ->willReturn($user);

        $queue->expects($this->once())
            ->method('getFirstPlace')
            ->willReturn($nextUser);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->once())
            ->method('persist')
            ->with($nextUser);

        $event = $this->createMock(QueuedUserRemovedEvent::class);
        $event->expects($this->once())
            ->method('isNotificationRequired')
            ->willReturn(true);

        $event->expects($this->once())
            ->method('getQueuedUser')
            ->willReturn($queuedUser);

        $event->expects($this->once())
            ->method('getQueue')
            ->willReturn($queue);

        $event->expects($this->once())
            ->method('isAutomatic')
            ->willReturn($automatic = $this->faker->boolean());

        $response = $this->createStub(SlackPrivateMessage::class);

        $messageFactory = $this->createMock(RemovedFromQueueMessageFactory::class);
        $messageFactory->expects($this->once())
            ->method('create')
            ->with($queuedUser, $queue, $automatic)
            ->willReturn($response);

        $firstInQueueMessageFactory = $this->createMock(FirstInQueueMessageFactory::class);
        $firstInQueueMessageFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $responseHandler = $this->createMock(PrivateMessageHandler::class);
        $responseHandler->expects($this->once())
            ->method('handle')
            ->with($response);

        $this
            ->expectsDebug('Handling removed event for queued user: {queue}', [
                'queue' => $queueId,
            ])
            ->expectsInfo('Sending notification to {userId}', [
                'userId' => $userId,
            ])
            ->expectsInfo('Next user in queue {queue} is the same as the previous user.', [
                'queue' => $queueId,
            ]);

        $subscriber = new QueuedUserEventSubscriber(
            $entityManager,
            $this->getLogger(),
            $responseHandler,
            $messageFactory,
            $firstInQueueMessageFactory,
            $calculator,
        );

        $subscriber->handleRemoved($event);
    }

    #[Test]
    public function itShouldSetExpiryOnFirstPersonInQueueAndPersistTheEntity(): void
    {
        $user = $this->createMock(User::class);
        $user->expects($this->once())
            ->method('getSlackId')
            ->willReturn($userId = 'userId');

        $queuedUser = $this->createMock(QueuedUser::class);
        $queuedUser->expects($this->exactly(2))
            ->method('getUser')
            ->willReturn($user);

        $collection = $this->createMock(Collection::class);
        $collection->expects($this->once())
            ->method('count')
            ->willReturn(1);

        $queue = $this->createMock(Queue::class);
        $queue->expects($this->once())
            ->method('removeQueuedUser')
            ->with($queuedUser)
            ->willReturnSelf();

        $queue->expects($this->once())
            ->method('getQueuedUsers')
            ->willReturn($collection);

        $queue->expects($this->exactly(2))
            ->method('getId')
            ->willReturn($queueId = 1);

        $nextUser = $this->createMock(QueuedUser::class);
        $nextUser->expects($this->once())
            ->method('getExpiryMinutes')
            ->willReturn($expiry = 15);

        $expectedExpiry = CarbonImmutable::now()->addMinutes($expiry);

        $calculator = $this->createMock(ClosestFiveMinutesCalculator::class);
        $calculator->expects($this->once())
            ->method('calculate')
            ->willReturnCallback(function ($argument) use ($expectedExpiry) {
                $this->assertInstanceOf(CarbonImmutable::class, $argument);

                return $expectedExpiry;
            });

        $nextUser->expects($this->once())
            ->method('setExpiresAt')
            ->willReturnCallback(function ($argument) use ($expectedExpiry, $nextUser, $queueId) {
                $this->assertInstanceOf(CarbonImmutable::class, $argument);

                $this->assertTrue($expectedExpiry->equalTo($argument));

                $this->expectsInfo('Set expiry on next user in queue {queue} to {expiresAt}.', [
                    'queue' => $queueId,
                    'expiresAt' => $argument->toIso8601ZuluString(),
                ]);

                return $nextUser;
            });

        $nextUser->expects($this->once())
            ->method('getUser')
            ->willReturn($this->createStub(User::class));

        $queue->expects($this->once())
            ->method('getFirstPlace')
            ->willReturn($nextUser);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->once())
            ->method('persist')
            ->with($nextUser);

        $event = $this->createMock(QueuedUserRemovedEvent::class);
        $event->expects($this->once())
            ->method('isNotificationRequired')
            ->willReturn(true);

        $event->expects($this->once())
            ->method('getQueuedUser')
            ->willReturn($queuedUser);

        $event->expects($this->once())
            ->method('getQueue')
            ->willReturn($queue);

        $event->expects($this->once())
            ->method('isAutomatic')
            ->willReturn($automatic = $this->faker->boolean());

        $response = $this->createStub(SlackPrivateMessage::class);

        $messageFactory = $this->createMock(RemovedFromQueueMessageFactory::class);
        $messageFactory->expects($this->once())
            ->method('create')
            ->with($queuedUser, $queue, $automatic)
            ->willReturn($response);

        $firstInQueueMessageFactory = $this->createMock(FirstInQueueMessageFactory::class);
        $firstInQueueMessageFactory->expects($this->once())
            ->method('create')
            ->with($nextUser)
            ->willReturn($firstInQueueMessage = $this->createStub(SlackPrivateMessage::class));

        $callCount = 0;

        $responseHandler = $this->createMock(PrivateMessageHandler::class);
        $responseHandler->expects($this->exactly(2))
            ->method('handle')
            ->willReturnCallback(function ($argument) use (&$callCount, $response, $firstInQueueMessage) {
                if (1 === ++$callCount) {
                    $this->assertSame($response, $argument);

                    return;
                }

                $this->assertSame($firstInQueueMessage, $argument);
            });

        $this
            ->expectsDebug('Handling removed event for queued user: {queue}', [
                'queue' => $queueId,
            ])
            ->expectsInfo('Sending notification to {userId}', [
                'userId' => $userId,
            ]);

        $subscriber = new QueuedUserEventSubscriber(
            $entityManager,
            $this->getLogger(),
            $responseHandler,
            $messageFactory,
            $firstInQueueMessageFactory,
            $calculator
        );

        $subscriber->handleRemoved($event);
    }
}

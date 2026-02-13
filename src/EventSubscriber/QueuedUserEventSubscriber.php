<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Calculator\ClosestFiveMinutesCalculator;
use App\Entity\DeploymentQueue;
use App\Event\QueuedUser\QueuedUserCreatedEvent;
use App\Event\QueuedUser\QueuedUserRemovedEvent;
use App\Slack\Response\PrivateMessage\Factory\FirstInQueueMessageFactory;
use App\Slack\Response\PrivateMessage\Factory\RemovedFromQueueMessageFactory;
use App\Slack\Response\PrivateMessage\PrivateMessageHandler;
use Carbon\CarbonImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

readonly class QueuedUserEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger,
        private PrivateMessageHandler $privateMessageHandler,
        private RemovedFromQueueMessageFactory $removedFromQueueMessageFactory,
        private FirstInQueueMessageFactory $firstInQueueMessageFactory,
        private ClosestFiveMinutesCalculator $closestFiveMinutesCalculator,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            QueuedUserCreatedEvent::class => 'handleCreated',
            QueuedUserRemovedEvent::class => 'handleRemoved',
        ];
    }

    public function handleCreated(QueuedUserCreatedEvent $event): void
    {
        $queue = ($queuedUser = $event->getQueuedUser())->getQueue();

        if (null === $queue) {
            $this->logger->error('Queued user {queuedUser} does not have a queue set.', [
                'queuedUser' => $queuedUser->getId(),
            ]);

            return;
        }

        if ($queue instanceof DeploymentQueue) {
            return;
        }

        $this->logger->debug('Handling created event for queued user: {queue}', [
            'queue' => $queue->getId(),
        ]);

        if (($expiry = $queuedUser->getExpiryMinutes()) === null) {
            return;
        }

        $this->logger->debug('{count} users in the queue.', [
            'count' => $queue->getQueuedUsers()->count(),
        ]);

        if (1 === $queue->getQueuedUsers()->count()) {
            $queuedUser->setExpiresAt(
                $expiresAt = $this->closestFiveMinutesCalculator->calculate(
                    CarbonImmutable::now()->addMinutes($expiry),
                ),
            );

            $this->logger->info('User is in first place, setting expiry time to {expiresAt}.', [
                'expiresAt' => $expiresAt->toIso8601ZuluString(),
            ]);
        }

        $this->entityManager->persist($queuedUser);
    }

    public function handleRemoved(QueuedUserRemovedEvent $event): void
    {
        $this->logger->debug('Handling removed event for queued user: {queue}', [
            'queue' => ($queue = $event->getQueue())->getId(),
        ]);

        if ($queue instanceof DeploymentQueue) {
            return;
        }

        $queuedUser = $event->getQueuedUser();

        $queue->removeQueuedUser($queuedUser);

        if ($event->isNotificationRequired()) {
            $this->logger->info('Sending notification to {userId}', [
                'userId' => $queuedUser->getUser()?->getSlackId() ?? '',
            ]);

            $this->privateMessageHandler->handle(
                $this->removedFromQueueMessageFactory->create($queuedUser, $queue, $event->isAutomatic()),
            );
        }

        if (0 === $queue->getQueuedUsers()->count()) {
            $this->logger->info('Queue {queue} does not have any queued users.', [
                'queue' => $queue->getId(),
            ]);

            return;
        }

        $nextUser = $queue->getFirstPlace();

        if (null === $nextUser) {
            $this->logger->warning(
                'Queue {queue} returned non-zero count on its queued users, but could not resolve next user.',
                [
                    'queue' => $queue->getId(),
                ]
            );

            return;
        }

        if (($expiry = $nextUser->getExpiryMinutes()) === null) {
            $this->logger->info('Next queued user on queue {queue} has no expiry set.', [
                'queue' => $queue->getId(),
            ]);

            return;
        }

        $nextUser->setExpiresAt(
            $expiresAt = $this->closestFiveMinutesCalculator->calculate(CarbonImmutable::now()->addMinutes($expiry)),
        );

        $this->logger->info('Set expiry on next user in queue {queue} to {expiresAt}.', [
            'queue' => $queue->getId(),
            'expiresAt' => $expiresAt->toIso8601ZuluString(),
        ]);

        $this->entityManager->persist($nextUser);

        if ($nextUser->getUser() === $queuedUser->getUser()) {
            $this->logger->info('Next user in queue {queue} is the same as the previous user.', [
                'queue' => $queue->getId(),
            ]);

            return;
        }

        $this->privateMessageHandler->handle(
            $this->firstInQueueMessageFactory->create($nextUser),
        );
    }
}

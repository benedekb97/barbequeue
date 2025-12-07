<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Event\QueuedUser\QueuedUserCreatedEvent;
use App\Event\QueuedUser\QueuedUserRemovedEvent;
use App\Slack\Response\Common\Factory\RemovedFromQueueMessageFactory;
use App\Slack\Response\Common\PrivateMessageResponseHandler;
use Carbon\CarbonImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

readonly class QueuedUserEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger,
        private PrivateMessageResponseHandler $privateMessageResponseHandler,
        private RemovedFromQueueMessageFactory $removedFromQueueMessageFactory,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            QueuedUserCreatedEvent::class => 'handleCreated',
            QueuedUserRemovedEvent::class => 'handleRemoved',
        ];
    }

    public function handleCreated(QueuedUserCreatedEvent $event): void
    {
        $this->logger->debug('Handling pre persist event for queued user');

        $queue = ($queuedUser = $event->getQueuedUser())->getQueue();

        if ($queue?->getExpiryMinutes() === null) {
            return;
        }

        $this->logger->debug('Count of users in queue: '.$queue->getQueuedUsers()->count());

        if ($queue->getQueuedUsers()->count() === 1) {
            $this->logger->debug('User is in first place');

            $queuedUser->setExpiresAt(
                CarbonImmutable::now()->addMinutes($queue->getExpiryMinutes())
            );
        }

        $this->entityManager->persist($queuedUser);
    }

    public function handleRemoved(QueuedUserRemovedEvent $event): void
    {
        $this->logger->debug('Handling pre-remove event for queued user');

        $queuedUser = $event->getQueuedUser();

        $queue = $event->getQueue();

        $queue->removeQueuedUser($queuedUser);

        if ($queue->getExpiryMinutes() === null) {
            return;
        }

        if ($queue->getQueuedUsers()->count() === 0) {
            return;
        }

        $nextUser = $queue->getFirstPlace()?->setExpiresAt(
            CarbonImmutable::now()->addMinutes($queue->getExpiryMinutes())
        );

        if ($nextUser !== null) {
            $this->entityManager->persist($nextUser);
        }


        if ($event->isNotificationRequired()) {
            $this->privateMessageResponseHandler->handle(
                $this->removedFromQueueMessageFactory->create($queuedUser, $queue, $event->isAutomatic()),
            );
        }
    }
}

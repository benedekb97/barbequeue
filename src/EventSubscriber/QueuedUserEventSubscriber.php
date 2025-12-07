<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Event\QueuedUser\QueuedUserCreatedEvent;
use App\Event\QueuedUser\QueuedUserRemovedEvent;
use Carbon\CarbonImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

readonly class QueuedUserEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger,
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

        if ($queue->getExpiryMinutes() === null) {
            return;
        }

        if ($queuedUser === $queue->getFirstPlace()) {
            $queuedUser->setExpiresAt(
                CarbonImmutable::now()->addMinutes($queue->getExpiryMinutes())
            );
        }

        $this->entityManager->persist($queuedUser);
    }

    public function handleRemoved(QueuedUserRemovedEvent $event): void
    {
        $this->logger->debug('Handling pre-remove event for queued user');

        $queue = ($queuedUser = $event->getQueuedUser())->getQueue();

        $queue->removeQueuedUser($queuedUser);

        if ($queue->getExpiryMinutes() === null) {
            return;
        }

        $queuedUser = $queue->getFirstPlace()->setExpiresAt(
            CarbonImmutable::now()->addMinutes($queue->getExpiryMinutes())
        );

        $this->entityManager->persist($queuedUser);
    }
}

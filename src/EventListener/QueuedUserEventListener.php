<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Event\QueuedUserEvent;
use Carbon\CarbonImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: QueuedUserEvent::CREATED, method: 'handleCreated')]
#[AsEventListener(event: QueuedUserEvent::REMOVED, method: 'handleRemoved')]
readonly class QueuedUserEventListener
{
    public function __construct(
        private LoggerInterface $logger,
        private EntityManagerInterface $entityManager,
    ) {}

    public function handleRemoved(QueuedUserEvent $event): void
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

    public function handleCreated(QueuedUserEvent $event): void
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
}

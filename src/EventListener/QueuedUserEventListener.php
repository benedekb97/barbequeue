<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\QueuedUser;
use Carbon\CarbonImmutable;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreRemoveEventArgs;
use Doctrine\ORM\Events;
use Psr\Log\LoggerInterface;

#[AsEntityListener(event: Events::preRemove, method: 'handlePreRemove', entity: QueuedUser::class)]
#[AsEntityListener(event: Events::prePersist, method: 'handlePrePersist', entity: QueuedUser::class)]
readonly class QueuedUserEventListener
{
    public function __construct(
        private LoggerInterface $logger,
    ) {}

    public function handlePreRemove(QueuedUser $queuedUser, PreRemoveEventArgs $eventArgs): void
    {
        $this->logger->debug('Handling pre-remove event for queued user');

        $queue = $queuedUser->getQueue();

        $queue->removeQueuedUser($queuedUser);

        if ($queue->getExpiryMinutes() === null) {
            return;
        }

        $queuedUser = $queue->getFirstPlace()->setExpiresAt(
            CarbonImmutable::now()->addMinutes($queue->getExpiryMinutes())
        );

        $eventArgs->getObjectmanager()->persist($queuedUser);
    }

    public function handlePrePersist(QueuedUser $queuedUser, PrePersistEventArgs $eventArgs): void
    {
        $this->logger->debug('Handling pre persist event for queued user');

        $queue = $queuedUser->getQueue();

        if ($queue->getExpiryMinutes() === null) {
            return;
        }

        if ($queuedUser === $queue->getFirstPlace()) {
            $queuedUser->setExpiresAt(
                CarbonImmutable::now()->addMinutes($queue->getExpiryMinutes())
            );
        }
    }
}

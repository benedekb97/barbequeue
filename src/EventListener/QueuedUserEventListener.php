<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\QueuedUser;
use Carbon\CarbonImmutable;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreRemoveEventArgs;
use Doctrine\ORM\Events;

#[AsEntityListener(event: Events::preRemove, method: 'handlePreRemove', entity: QueuedUser::class)]
#[AsEntityListener(event: Events::prePersist, method: 'handlePrePersist', entity: QueuedUser::class)]
readonly class QueuedUserEventListener
{
    public function handlePreRemove(QueuedUser $queuedUser, PreRemoveEventArgs $eventArgs): void
    {
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

<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\Queue;
use Carbon\CarbonImmutable;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;

#[AsEntityListener(event: Events::preUpdate, method: 'handlePreUpdate', entity: Queue::class)]
class QueueEventListener
{
    public function handlePreUpdate(Queue $queue, PreUpdateEventArgs $eventArgs): void
    {
        $user = $queue->getFirstPlace();

        $user->setExpiresAt(
            ($expiryTime = $queue->getExpiryMinutes())
                ? CarbonImmutable::createFromInterface($user->getCreatedAt())->addMinutes($expiryTime)
                : null
        );

        $eventArgs->getObjectManager()->persist($user);
    }
}

<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\Queue;
use Carbon\CarbonImmutable;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Psr\Log\LoggerInterface;

#[AsEntityListener(event: Events::preUpdate, method: 'handlePreUpdate', entity: Queue::class)]
readonly class QueueEventListener
{
    public function __construct(
        private LoggerInterface $logger,
    ) {}

    public function handlePreUpdate(Queue $queue, PreUpdateEventArgs $eventArgs): void
    {
        $this->logger->debug('Handling queue update event for queue '.$queue->getName());

        $user = $queue->getFirstPlace();

        $user->setExpiresAt(
            ($expiryTime = $queue->getExpiryMinutes())
                ? CarbonImmutable::createFromInterface($user->getCreatedAt())->addMinutes($expiryTime)
                : null
        );

        $eventArgs->getObjectManager()->persist($user);
    }
}

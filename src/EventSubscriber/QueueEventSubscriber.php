<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Event\Queue\QueueUpdatedEvent;
use Carbon\CarbonImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

readonly class QueueEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            QueueUpdatedEvent::class => 'handleUpdated',
        ];
    }

    public function handleUpdated(QueueUpdatedEvent $event): void
    {
        $queue = $event->getQueue();

        $this->logger->debug('Handling queue update event for queue '.$queue->getName());

        $user = $queue->getFirstPlace();

        if ($user === null) {
            $this->logger->debug('No user found to update on queue '.$queue->getName());
            return;
        }

        $user->setExpiresAt(
            ($expiryTime = $queue->getExpiryMinutes())
                ? CarbonImmutable::createFromInterface($user->getCreatedAt())->addMinutes($expiryTime)
                : null
        );

        $this->logger->debug('Set expiry time for user to '.$user->getExpiresAt()->toDateTimeString());

        $this->entityManager->persist($user);
    }
}

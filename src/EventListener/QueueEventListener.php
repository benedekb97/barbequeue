<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Event\QueueEvent;
use Carbon\CarbonImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: QueueEvent::UPDATED, method: 'handleUpdated')]
readonly class QueueEventListener
{
    public function __construct(
        private LoggerInterface $logger,
        private EntityManagerInterface $entityManager,
    ) {}

    public function handleUpdated(QueueEvent $event): void
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

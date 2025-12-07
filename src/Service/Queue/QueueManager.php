<?php

declare(strict_types=1);

namespace App\Service\Queue;

use App\Entity\Queue;
use App\Entity\QueuedUser;
use App\Event\Queue\QueueUpdatedEvent;
use App\Event\QueuedUser\QueuedUserCreatedEvent;
use App\Event\QueuedUser\QueuedUserRemovedEvent;
use App\Repository\QueueRepositoryInterface;
use App\Service\Queue\Exception\QueueNotFoundException;
use App\Service\Queue\Exception\UnableToJoinQueueException;
use App\Service\Queue\Exception\UnableToLeaveQueueException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Psr\EventDispatcher\EventDispatcherInterface;

readonly class QueueManager
{
    public function __construct(
        private QueueRepositoryInterface $queueRepository,
        private EntityManagerInterface $entityManager,
        private EventDispatcherInterface $eventDispatcher,
    ) {
    }

    /**
     * @throws QueueNotFoundException
     * @throws UnableToJoinQueueException
     */
    public function joinQueue(string $queueName, string $domain, string $userId): Queue
    {
        $queue = $this->queueRepository->findOneByNameAndDomain($queueName, $domain);

        if ($queue === null) {
            throw new QueueNotFoundException($queueName, $domain, $userId);
        }

        if (!$queue->canJoin($userId)) {
            throw new UnableToJoinQueueException($queue);
        }

        $queuedUser = new QueuedUser()
            ->setUserId($userId);

        $queue->addQueuedUser($queuedUser);

        $this->eventDispatcher->dispatch(new QueuedUserCreatedEvent($queuedUser));

        $this->entityManager->persist($queuedUser);
        $this->entityManager->flush();

        return $queue;
    }

    /**
     * @throws QueueNotFoundException
     * @throws UnableToLeaveQueueException
     */
    public function leaveQueue(string $queueName, string $domain, string $userId): Queue
    {
        $queue = $this->queueRepository->findOneByNameAndDomain($queueName, $domain);

        if ($queue === null) {
            throw new QueueNotFoundException($queueName, $domain, $userId);
        }

        if (!$queue->canLeave($userId)) {
            throw new UnableToLeaveQueueException($queue);
        }

        /** @var QueuedUser $queuedUser */
        $queuedUser = $queue->getLastPlace($userId);

        $queue->removeQueuedUser($queuedUser);

        $this->eventDispatcher->dispatch(new QueuedUserRemovedEvent($queuedUser, $queue));

        $this->entityManager->remove($queuedUser);
        $this->entityManager->flush();

        return $queue;
    }

    /** @throws EntityNotFoundException */
    public function editQueue(int $queueId, ?int $maximumEntriesPerUser, ?int $expiryMinutes): Queue
    {
        $queue = $this->queueRepository->find($queueId);

        if ($queue === null) {
            throw new EntityNotFoundException("Queue with id $queueId not found");
        }

        $queue->setMaximumEntriesPerUser($maximumEntriesPerUser)
            ->setExpiryMinutes($expiryMinutes);

        $this->eventDispatcher->dispatch(new QueueUpdatedEvent($queue));

        $this->entityManager->persist($queue);
        $this->entityManager->flush();

        return $queue;
    }

    public function popQueue(string $queueName, string $domain): Queue
    {
        $queue = $this->queueRepository->findOneByNameAndDomain($queueName, $domain);

        if ($queue === null) {
            throw new QueueNotFoundException($queueName, $domain, '');
        }

        $queuedUser = $queue->getFirstPlace();

        $queue->removeQueuedUser($queuedUser);

        $this->eventDispatcher->dispatch(new QueuedUserRemovedEvent($queuedUser, $queue, true));

        $this->entityManager->remove($queuedUser);
        $this->entityManager->flush();

        return $queue;
    }
}

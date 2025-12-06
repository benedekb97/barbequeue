<?php

declare(strict_types=1);

namespace App\Service\Queue;

use App\Entity\Queue;
use App\Entity\QueuedUser;
use App\Repository\QueueRepositoryInterface;
use App\Service\Queue\Exception\QueueNotFoundException;
use App\Service\Queue\Exception\UnableToJoinQueueException;
use App\Service\Queue\Exception\UnableToLeaveQueueException;
use Doctrine\ORM\EntityManagerInterface;

readonly class QueueManager
{
    public function __construct(
        private QueueRepositoryInterface $queueRepository,
        private EntityManagerInterface $entityManager,
    ) {}

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

        $queuedUser = $queue->getLastPlace($userId);

        $queue->removeQueuedUser($queuedUser);

        $this->entityManager->remove($queuedUser);
        $this->entityManager->flush();

        return $queue;
    }
}

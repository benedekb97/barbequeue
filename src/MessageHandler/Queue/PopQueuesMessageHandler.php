<?php

declare(strict_types=1);

namespace App\MessageHandler\Queue;

use App\Event\QueuedUser\QueuedUserRemovedEvent;
use App\Message\Queue\PopQueuesMessage;
use App\Repository\QueuedUserRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class PopQueuesMessageHandler
{
    public function __construct(
        private QueuedUserRepositoryInterface $repository,
        private EntityManagerInterface $entityManager,
        private EventDispatcherInterface $eventDispatcher,
    ) {}

    public function __invoke(PopQueuesMessage $message): void
    {
        $users = $this->repository->findAllExpired();

        foreach ($users as $queuedUser) {
            $this->entityManager->remove($queuedUser);

            $this->eventDispatcher->dispatch(
                new QueuedUserRemovedEvent($queuedUser, $queuedUser->getQueue(), true)
            );
        }

        $this->entityManager->flush();
    }
}

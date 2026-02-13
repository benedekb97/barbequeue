<?php

declare(strict_types=1);

namespace App\MessageHandler\Queue;

use App\Entity\Deployment;
use App\Entity\Repository;
use App\Entity\Workspace;
use App\Event\Deployment\DeploymentCompletedEvent;
use App\Event\QueuedUser\QueuedUserRemovedEvent;
use App\Event\Repository\RepositoryUpdatedEvent;
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
    ) {
    }

    public function __invoke(PopQueuesMessage $message): void
    {
        $users = $this->repository->findAllExpired();

        foreach ($users as $queuedUser) {
            $this->entityManager->remove($queuedUser);

            $queue = $queuedUser->getQueue();

            if (null === $queue) {
                continue;
            }

            $queue->removeQueuedUser($queuedUser);

            $this->eventDispatcher->dispatch(
                new QueuedUserRemovedEvent($queuedUser, $queue, true, true)
            );

            if ($queuedUser instanceof Deployment) {
                /** @var Repository $repository */
                $repository = $queuedUser->getRepository();

                $repository->removeDeployment($queuedUser);

                /** @var Workspace $workspace */
                $workspace = $queue->getWorkspace();

                $this->eventDispatcher->dispatch(new DeploymentCompletedEvent($queuedUser, $workspace, $repository, true));

                foreach ($workspace->getRepositories() as $repository) {
                    if ($repository->isBlockedByDeployment()) {
                        continue;
                    }

                    $this->eventDispatcher->dispatch(new RepositoryUpdatedEvent($repository, true));
                }
            }
        }

        $this->entityManager->flush();
    }
}

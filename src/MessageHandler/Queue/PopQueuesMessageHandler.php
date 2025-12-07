<?php

declare(strict_types=1);

namespace App\MessageHandler\Queue;

use App\Message\Queue\PopQueuesMessage;
use App\Repository\QueuedUserRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class PopQueuesMessageHandler
{
    public function __construct(
        private QueuedUserRepositoryInterface $repository,
        private EntityManagerInterface $entityManager,
    ) {}

    public function __invoke(PopQueuesMessage $message): void
    {
        $users = $this->repository->findAllExpired();

        foreach ($users as $queuedUser) {
            $this->entityManager->remove($queuedUser);
        }

        $this->entityManager->flush();
    }
}

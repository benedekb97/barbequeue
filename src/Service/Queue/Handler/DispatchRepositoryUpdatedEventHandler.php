<?php

declare(strict_types=1);

namespace App\Service\Queue\Handler;

use App\Entity\Deployment;
use App\Event\Repository\RepositoryUpdatedEvent;
use App\Service\Queue\Context\QueueContextInterface;
use App\Service\Queue\Leave\Handler\LeaveQueueHandlerInterface;
use App\Service\Queue\Leave\LeaveQueueContext;
use App\Service\Queue\Pop\Handler\PopQueueHandlerInterface;
use App\Service\Queue\Pop\PopQueueContext;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem(priority: 5_500)]
readonly class DispatchRepositoryUpdatedEventHandler implements LeaveQueueHandlerInterface, PopQueueHandlerInterface
{
    public function __construct(
        private EventDispatcherInterface $eventDispatcher,
        private LoggerInterface $logger,
    ) {
    }

    public function supports(QueueContextInterface $context): bool
    {
        return ($context instanceof PopQueueContext || $context instanceof LeaveQueueContext)
            && $context->getQueuedUser() instanceof Deployment;
    }

    public function handle(QueueContextInterface $context): void
    {
        if (!$context instanceof LeaveQueueContext && !$context instanceof PopQueueContext) {
            return;
        }

        foreach ($context->getWorkspace()->getRepositories() as $repository) {
            if ($repository->isBlockedByDeployment()) {
                continue;
            }

            $this->logger->debug('Dispatching repository updated event for {repository} {contextId} {contextType}', [
                'repository' => $repository->getId(),
                'contextId' => $context->getId(),
                'contextType' => $context->getType()->value,
            ]);

            $this->eventDispatcher->dispatch(new RepositoryUpdatedEvent($repository, true));
        }
    }
}

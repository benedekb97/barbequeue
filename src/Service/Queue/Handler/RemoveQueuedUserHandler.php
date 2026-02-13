<?php

declare(strict_types=1);

namespace App\Service\Queue\Handler;

use App\Service\Queue\Context\ContextType;
use App\Service\Queue\Context\QueueContextInterface;
use App\Service\Queue\Leave\Handler\LeaveQueueHandlerInterface;
use App\Service\Queue\Leave\LeaveQueueContext;
use App\Service\Queue\Pop\Handler\PopQueueHandlerInterface;
use App\Service\Queue\Pop\PopQueueContext;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem(priority: 5_800)]
readonly class RemoveQueuedUserHandler implements LeaveQueueHandlerInterface, PopQueueHandlerInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger,
    ) {
    }

    public function supports(QueueContextInterface $context): bool
    {
        return in_array($context->getType(), [ContextType::LEAVE, ContextType::POP]);
    }

    public function handle(QueueContextInterface $context): void
    {
        if (!$context instanceof LeaveQueueContext && !$context instanceof PopQueueContext) {
            return;
        }

        $this->logger->debug('Removing {queuedUser} from {queue} {contextId} {contextType}', [
            'queuedUser' => ($queuedUser = $context->getQueuedUser())->getId(),
            'queue' => ($queue = $context->getQueue())->getId(),
            'contextId' => $context->getId(),
            'contextType' => $context->getType()->value,
        ]);

        $queue->removeQueuedUser($queuedUser);

        $this->entityManager->remove($queuedUser);
    }
}

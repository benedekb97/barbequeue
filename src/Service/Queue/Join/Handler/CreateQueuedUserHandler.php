<?php

declare(strict_types=1);

namespace App\Service\Queue\Join\Handler;

use App\Entity\DeploymentQueue;
use App\Factory\QueuedUserFactory;
use App\Service\Queue\Context\QueueContextInterface;
use App\Service\Queue\Join\JoinQueueContext;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem(priority: 4_500)]
readonly class CreateQueuedUserHandler implements JoinQueueHandlerInterface
{
    public function __construct(
        private QueuedUserFactory $queuedUserFactory,
        private LoggerInterface $logger,
    ) {
    }

    public function supports(QueueContextInterface $context): bool
    {
        return !$context->getQueue() instanceof DeploymentQueue;
    }

    public function handle(QueueContextInterface $context): void
    {
        if (!$context instanceof JoinQueueContext) {
            return;
        }

        $this->logger->debug('Creating queued user for simple {queue} {contextId} {contextType}', [
            'queue' => ($queue = $context->getQueue())->getId(),
            'contextId' => $context->getId(),
            'contextType' => $context->getType()->value,
        ]);

        $queuedUser = $this->queuedUserFactory->createForQueue($queue);
        $queuedUser->setUser($context->getUser());

        $context->setQueuedUser($queuedUser);
    }
}

<?php

declare(strict_types=1);

namespace App\Service\Queue\Join\Handler;

use App\Service\Queue\Context\ContextType;
use App\Service\Queue\Context\QueueContextInterface;
use App\Service\Queue\Exception\UnableToJoinQueueException;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem(priority: 5_000)]
readonly class CanJoinQueueHandler implements JoinQueueHandlerInterface
{
    public function __construct(
        private LoggerInterface $logger,
    ) {
    }

    public function supports(QueueContextInterface $context): bool
    {
        return ContextType::JOIN === $context->getType();
    }

    /** @throws UnableToJoinQueueException */
    public function handle(QueueContextInterface $context): void
    {
        $this->logger->debug('Checking whether {user} can join {queue} for {contextId} {contextType}', [
            'user' => $userId = $context->getUserId(),
            'queue' => ($queue = $context->getQueue())->getId(),
            'contextId' => $context->getId(),
            'contextType' => $context->getType()->value,
        ]);

        if (!$queue->canJoin($userId)) {
            throw new UnableToJoinQueueException($queue);
        }
    }
}

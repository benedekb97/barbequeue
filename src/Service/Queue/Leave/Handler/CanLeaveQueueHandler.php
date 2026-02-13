<?php

declare(strict_types=1);

namespace App\Service\Queue\Leave\Handler;

use App\Service\Queue\Context\ContextType;
use App\Service\Queue\Context\QueueContextInterface;
use App\Service\Queue\Exception\UnableToLeaveQueueException;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem(priority: 6_000)]
readonly class CanLeaveQueueHandler implements LeaveQueueHandlerInterface
{
    public function __construct(
        private LoggerInterface $logger,
    ) {
    }

    public function supports(QueueContextInterface $context): bool
    {
        return ContextType::LEAVE === $context->getType();
    }

    /** @throws UnableToLeaveQueueException */
    public function handle(QueueContextInterface $context): void
    {
        $this->logger->debug('Checking whether {user} can leave {queue} for {contextId} {contextType}', [
            'user' => $userId = $context->getUserId(),
            'queue' => ($queue = $context->getQueue())->getId(),
            'contextId' => $context->getId(),
            'contextType' => $context->getType()->value,
        ]);

        if (!$queue->canLeave($userId)) {
            throw new UnableToLeaveQueueException($queue);
        }
    }
}

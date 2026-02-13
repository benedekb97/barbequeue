<?php

declare(strict_types=1);

namespace App\Service\Queue\Pop\Handler;

use App\Service\Queue\Context\ContextType;
use App\Service\Queue\Context\QueueContextInterface;
use App\Service\Queue\Exception\QueueEmptyException;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem(priority: 6_000)]
readonly class CanPopQueueHandler implements PopQueueHandlerInterface
{
    public function __construct(
        private LoggerInterface $logger,
    ) {
    }

    public function supports(QueueContextInterface $context): bool
    {
        return ContextType::POP === $context->getType();
    }

    /** @throws QueueEmptyException */
    public function handle(QueueContextInterface $context): void
    {
        if (($queue = $context->getQueue())->getQueuedUsers()->isEmpty()) {
            $this->logger->debug('{queue} is empty, exiting {contextId} {contextType}', [
                'queue' => $queue->getId(),
                'contextId' => $context->getId(),
                'contextType' => $context->getType()->value,
            ]);

            throw new QueueEmptyException($queue);
        }
    }
}

<?php

declare(strict_types=1);

namespace App\Service\Queue\Edit\Handler;

use App\Repository\QueueRepositoryInterface;
use App\Service\Queue\Context\ContextType;
use App\Service\Queue\Context\QueueContextInterface;
use Doctrine\ORM\EntityNotFoundException;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem(priority: 2_000)]
readonly class FindQueueHandler implements EditQueueHandlerInterface
{
    public function __construct(
        private LoggerInterface $logger,
        private QueueRepositoryInterface $queueRepository,
    ) {
    }

    public function supports(QueueContextInterface $context): bool
    {
        return ContextType::EDIT === $context->getType();
    }

    /** @throws EntityNotFoundException */
    public function handle(QueueContextInterface $context): void
    {
        if (!is_numeric($queueId = $context->getQueueIdentifier())) {
            $this->logger->error('Expected integer queue identifier, received {queueId} on {contextId} {contextType}', [
                'queueId' => $queueId,
                'contextId' => $context->getId(),
                'contextType' => $context->getType()->value,
            ]);

            return;
        }

        $queueId = intval($queueId);

        $this->logger->debug('Finding {queueId} for {contextId} {contextType}', [
            'queueId' => $queueId,
            'contextId' => $context->getId(),
            'contextType' => $context->getType()->value,
        ]);

        $queue = $this->queueRepository->find($queueId);

        if (($workspace = $queue?->getWorkspace()) === null) {
            $this->logger->warning('Could not find {queueId} for {contextId} {contextType}', [
                'queueId' => $queueId,
                'contextId' => $context->getId(),
                'contextType' => $context->getType()->value,
            ]);

            throw new EntityNotFoundException("Queue with id $queueId not found");
        }

        $context->setQueue($queue);
        $context->setWorkspace($workspace);
    }
}

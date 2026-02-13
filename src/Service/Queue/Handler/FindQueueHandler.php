<?php

declare(strict_types=1);

namespace App\Service\Queue\Handler;

use App\Repository\QueueRepositoryInterface;
use App\Service\Queue\Context\QueueContextInterface;
use App\Service\Queue\Exception\QueueNotFoundException;
use App\Service\Queue\Join\Handler\JoinQueueHandlerInterface;
use App\Service\Queue\Leave\Handler\LeaveQueueHandlerInterface;
use App\Service\Queue\Pop\Handler\PopQueueHandlerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem(priority: 10_000)]
readonly class FindQueueHandler implements JoinQueueHandlerInterface, LeaveQueueHandlerInterface, PopQueueHandlerInterface
{
    public function __construct(
        private QueueRepositoryInterface $queueRepository,
        private LoggerInterface $logger,
    ) {
    }

    public function supports(QueueContextInterface $context): bool
    {
        return !$context->hasQueue() || !$context->hasWorkspace();
    }

    /** @throws QueueNotFoundException */
    public function handle(QueueContextInterface $context): void
    {
        $this->logger->debug('Finding queue by {queueName} and {workspace} for {contextId} {contextType}', [
            'queueName' => $queueName = (string) $context->getQueueIdentifier(),
            'workspace' => $teamId = $context->getTeamId(),
            'contextId' => $context->getId(),
            'contextType' => $context->getType()->value,
        ]);

        $queue = $this->queueRepository->findOneByNameAndTeamid($queueName, $teamId);

        if (($workspace = $queue?->getWorkspace()) === null) {
            $this->logger->info('Queue not found by {queueName} and {workspace} for {contextId} {contextType}', [
                'queueName' => $queueName,
                'workspace' => $teamId,
                'contextId' => $context->getId(),
                'contextType' => $context->getType()->value,
            ]);

            throw new QueueNotFoundException($queueName, $teamId, $context->getUserId());
        }

        $context->setQueue($queue);
        $context->setWorkspace($workspace);
    }
}

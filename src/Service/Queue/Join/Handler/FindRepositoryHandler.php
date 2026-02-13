<?php

declare(strict_types=1);

namespace App\Service\Queue\Join\Handler;

use App\Entity\DeploymentQueue;
use App\Repository\RepositoryRepositoryInterface;
use App\Service\Queue\Context\QueueContextInterface;
use App\Service\Queue\Exception\DeploymentInformationRequiredException;
use App\Service\Queue\Join\JoinQueueContext;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem(priority: 4_800)]
readonly class FindRepositoryHandler implements JoinQueueHandlerInterface
{
    public function __construct(
        private RepositoryRepositoryInterface $repositoryRepository,
        private LoggerInterface $logger,
    ) {
    }

    public function supports(QueueContextInterface $context): bool
    {
        return $context instanceof JoinQueueContext
            && $context->getQueue() instanceof DeploymentQueue
            && null === $context->getRepository();
    }

    /** @throws DeploymentInformationRequiredException */
    public function handle(QueueContextInterface $context): void
    {
        if (!$context instanceof JoinQueueContext) {
            return;
        }

        if (!($queue = $context->getQueue()) instanceof DeploymentQueue) {
            return;
        }

        $this->logger->debug('Finding repository for {queue} on {contextId} {contextType}', [
            'queue' => $queue->getId(),
            'contextId' => $context->getId(),
            'contextType' => $context->getType()->value,
        ]);

        $repository = $this->repositoryRepository->find($context->getDeploymentRepositoryId());

        if (null === $repository) {
            throw new DeploymentInformationRequiredException($queue);
        }

        $context->setRepository($repository);
    }
}

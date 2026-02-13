<?php

declare(strict_types=1);

namespace App\Service\Queue\Edit\Handler;

use App\Entity\DeploymentQueue;
use App\Repository\RepositoryRepositoryInterface;
use App\Service\Queue\Context\QueueContextInterface;
use App\Service\Queue\Edit\EditQueueContext;
use App\Service\Repository\Exception\RepositoryNotFoundException;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem(priority: 1_800)]
readonly class FindRepositoriesHandler implements EditQueueHandlerInterface
{
    public function __construct(
        private RepositoryRepositoryInterface $repositoryRepository,
        private LoggerInterface $logger,
    ) {
    }

    public function supports(QueueContextInterface $context): bool
    {
        return $context->getQueue() instanceof DeploymentQueue;
    }

    /** @throws RepositoryNotFoundException */
    public function handle(QueueContextInterface $context): void
    {
        if (!$context instanceof EditQueueContext) {
            return;
        }

        if (empty($repositoryIds = $context->getRepositoryIds())) {
            $this->logger->error('No repository ids provided for {queue} on {contextId} {contextType}', [
                'queue' => $context->getQueue()->getId(),
                'contextId' => $context->getId(),
                'contextType' => $context->getType()->value,
            ]);

            throw new RepositoryNotFoundException();
        }

        $this->logger->debug('Resolving repositories for {queue} on {contextId} {contextType}', [
            'queue' => $context->getQueue()->getId(),
            'contextId' => $context->getId(),
            'contextType' => $context->getType()->value,
        ]);

        $repositories = $this->repositoryRepository->findByIdsAndWorkspace($repositoryIds, $context->getWorkspace());

        if (empty($repositories)) {
            $this->logger->error('Provided {repositoryIds} could not be resolved on {contextId} {contextType}', [
                'contextId' => $context->getId(),
                'contextType' => $context->getType()->value,
                'repositoryIds' => implode(', ', $repositoryIds),
            ]);

            throw new RepositoryNotFoundException();
        }

        foreach ($repositories as $repository) {
            $context->addRepository($repository);
        }
    }
}

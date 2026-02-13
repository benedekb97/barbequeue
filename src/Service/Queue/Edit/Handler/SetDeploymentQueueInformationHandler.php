<?php

declare(strict_types=1);

namespace App\Service\Queue\Edit\Handler;

use App\Entity\DeploymentQueue;
use App\Service\Queue\Context\QueueContextInterface;
use App\Service\Queue\Edit\EditQueueContext;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem(priority: 1_500)]
readonly class SetDeploymentQueueInformationHandler implements EditQueueHandlerInterface
{
    public function __construct(private LoggerInterface $logger)
    {
    }

    public function supports(QueueContextInterface $context): bool
    {
        return $context->getQueue() instanceof DeploymentQueue;
    }

    public function handle(QueueContextInterface $context): void
    {
        if (!$context instanceof EditQueueContext) {
            return;
        }

        /** @var DeploymentQueue $queue */
        $queue = $context->getQueue();

        $this->logger->debug('Setting deployment queue information for {queue} on {contextId} {contextType}', [
            'queue' => $queue->getId(),
            'contextId' => $context->getId(),
            'contextType' => $context->getType()->value,
        ]);

        $queue->setBehaviour($context->getBehaviour());

        $queue->clearRepositories();

        foreach ($context->getRepositories() as $repository) {
            $queue->addRepository($repository);
        }
    }
}

<?php

declare(strict_types=1);

namespace App\Service\Queue\Join\Handler;

use App\Entity\DeploymentQueue;
use App\Factory\DeploymentFactory;
use App\Service\Queue\Context\QueueContextInterface;
use App\Service\Queue\Join\JoinQueueContext;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem(priority: 4_400)]
readonly class CreateDeploymentHandler implements JoinQueueHandlerInterface
{
    public function __construct(
        private DeploymentFactory $deploymentFactory,
        private LoggerInterface $logger,
    ) {
    }

    public function supports(QueueContextInterface $context): bool
    {
        return $context->getQueue() instanceof DeploymentQueue;
    }

    public function handle(QueueContextInterface $context): void
    {
        if (!$context instanceof JoinQueueContext) {
            return;
        }

        if (!($queue = $context->getQueue()) instanceof DeploymentQueue) {
            return;
        }

        $this->logger->debug('Creating deployment for {queue} {contextId} {contextType}', [
            'queue' => $queue->getId(),
            'contextId' => $context->getId(),
            'contextType' => $context->getType()->value,
        ]);

        $deployment = $this->deploymentFactory->createForDeploymentQueue($queue);
        $deployment->setUser($context->getUser());

        $context->setQueuedUser($deployment);
    }
}

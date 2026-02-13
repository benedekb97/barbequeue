<?php

declare(strict_types=1);

namespace App\Service\Queue\Join\Handler;

use App\Entity\DeploymentQueue;
use App\Service\Queue\Context\ContextType;
use App\Service\Queue\Context\QueueContextInterface;
use App\Service\Queue\Exception\DeploymentInformationRequiredException;
use App\Service\Queue\Join\JoinQueueContext;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem(priority: 4_900)]
readonly class ValidateJoinDataHandler implements JoinQueueHandlerInterface
{
    public function __construct(
        private LoggerInterface $logger,
    ) {
    }

    public function supports(QueueContextInterface $context): bool
    {
        return ContextType::JOIN === $context->getType() && $context->getQueue() instanceof DeploymentQueue;
    }

    /** @throws DeploymentInformationRequiredException */
    public function handle(QueueContextInterface $context): void
    {
        if (!($queue = $context->getQueue()) instanceof DeploymentQueue) {
            return;
        }

        if (!$context instanceof JoinQueueContext) {
            return;
        }

        $this->logger->debug('Validating input data for {queue} {contextId} {contextType}', [
            'queue' => $queue->getId(),
            'contextId' => $context->getId(),
            'contextType' => $context->getType()->value,
        ]);

        if (empty($context->getDeploymentDescription())) {
            throw new DeploymentInformationRequiredException($queue);
        }

        if (empty($context->getDeploymentLink())) {
            throw new DeploymentInformationRequiredException($queue);
        }

        if (empty($context->getDeploymentRepositoryId())) {
            throw new DeploymentInformationRequiredException($queue);
        }
    }
}

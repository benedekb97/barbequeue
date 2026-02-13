<?php

declare(strict_types=1);

namespace App\Service\Queue\Edit\Handler;

use App\Entity\DeploymentQueue;
use App\Enum\QueueBehaviour;
use App\Service\Queue\Context\QueueContextInterface;
use App\Service\Queue\Edit\EditQueueContext;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem(priority: 1_600)]
readonly class ResolveBehaviourHandler implements EditQueueHandlerInterface
{
    public function __construct(
        private LoggerInterface $logger,
    ) {
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

        $behaviour = $context->getQueueBehaviour();

        $this->logger->debug('Resolving behaviour from {behaviour} for {queue} on {contextId} {contextType}', [
            'behaviour' => $behaviour,
            'queue' => $context->getQueue()->getId(),
            'contextId' => $context->getId(),
            'contextType' => $context->getType()->value,
        ]);

        $behaviour = QueueBehaviour::tryFrom($behaviour ?? '') ?? QueueBehaviour::ENFORCE_QUEUE;

        $context->setBehaviour($behaviour);
    }
}

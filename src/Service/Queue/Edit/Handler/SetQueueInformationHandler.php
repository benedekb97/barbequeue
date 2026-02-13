<?php

declare(strict_types=1);

namespace App\Service\Queue\Edit\Handler;

use App\Service\Queue\Context\ContextType;
use App\Service\Queue\Context\QueueContextInterface;
use App\Service\Queue\Edit\EditQueueContext;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem(priority: 1_700)]
readonly class SetQueueInformationHandler implements EditQueueHandlerInterface
{
    public function __construct(
        private LoggerInterface $logger,
    ) {
    }

    public function supports(QueueContextInterface $context): bool
    {
        return ContextType::EDIT === $context->getType();
    }

    public function handle(QueueContextInterface $context): void
    {
        if (!$context instanceof EditQueueContext) {
            return;
        }

        $this->logger->debug('Setting information on {queue} for {contextId} {contextType}', [
            'queue' => ($queue = $context->getQueue())->getId(),
            'contextId' => $context->getId(),
            'contextType' => $context->getType()->value,
        ]);

        $queue->setMaximumEntriesPerUser($context->getMaximumEntriesPerUser())
            ->setExpiryMinutes($context->getExpiryMinutes());
    }
}

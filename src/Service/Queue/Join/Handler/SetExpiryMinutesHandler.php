<?php

declare(strict_types=1);

namespace App\Service\Queue\Join\Handler;

use App\Service\Queue\Context\QueueContextInterface;
use App\Service\Queue\Join\JoinQueueContext;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem(priority: 4_300)]
readonly class SetExpiryMinutesHandler implements JoinQueueHandlerInterface
{
    public function __construct(
        private LoggerInterface $logger,
    ) {
    }

    public function supports(QueueContextInterface $context): bool
    {
        return $context instanceof JoinQueueContext && (
            null !== $context->getRequiredMinutes() || null !== $context->getQueue()->getExpiryMinutes()
        );
    }

    public function handle(QueueContextInterface $context): void
    {
        if (!$context instanceof JoinQueueContext) {
            return;
        }

        $queueExpiry = ($queue = $context->getQueue())->getExpiryMinutes();
        $requiredMinutes = $context->getRequiredMinutes();

        $expiryMinutes = match (true) {
            null !== $queueExpiry && null !== $requiredMinutes => min($requiredMinutes, $queueExpiry),
            null !== $queueExpiry => $queueExpiry,
            null !== $requiredMinutes => $requiredMinutes,
            default => null,
        };

        $this->logger->debug(
            'Setting expiry minutes for queued user on {queue} to {expiryMinutes} {contextId} {contextType}',
            [
                'queue' => $queue->getId(),
                'expiryMinutes' => $expiryMinutes,
                'contextId' => $context->getId(),
                'contextType' => $context->getType()->value,
            ],
        );

        $context->getQueuedUser()->setExpiryMinutes($expiryMinutes);
    }
}

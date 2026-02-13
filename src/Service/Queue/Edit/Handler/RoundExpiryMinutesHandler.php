<?php

declare(strict_types=1);

namespace App\Service\Queue\Edit\Handler;

use App\Service\Queue\Context\QueueContextInterface;
use App\Service\Queue\Edit\EditQueueContext;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem(priority: 1_900)]
readonly class RoundExpiryMinutesHandler implements EditQueueHandlerInterface
{
    public function __construct(
        private LoggerInterface $logger,
    ) {
    }

    public function supports(QueueContextInterface $context): bool
    {
        return $context instanceof EditQueueContext && null !== $context->getExpiryMinutes();
    }

    public function handle(QueueContextInterface $context): void
    {
        if (!$context instanceof EditQueueContext) {
            return;
        }

        $expiryMinutes = $context->getExpiryMinutes();

        $this->logger->debug(
            'Rounding up expiry minutes from {expiryMinutes} to closest 5 for {contextId} {contextType}',
            [
                'expiryMinutes' => $expiryMinutes,
                'contextId' => $context->getId(),
                'contextType' => $context->getType()->value,
            ],
        );

        if (0 === $expiryMinutes % 5) {
            return;
        }

        $context->setExpiryMinutes($expiryMinutes + (5 - $expiryMinutes % 5));
    }
}

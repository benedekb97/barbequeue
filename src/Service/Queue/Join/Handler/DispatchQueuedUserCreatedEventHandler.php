<?php

declare(strict_types=1);

namespace App\Service\Queue\Join\Handler;

use App\Event\QueuedUser\QueuedUserCreatedEvent;
use App\Service\Queue\Context\QueueContextInterface;
use App\Service\Queue\Join\JoinQueueContext;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem(priority: 3_000)]
readonly class DispatchQueuedUserCreatedEventHandler implements JoinQueueHandlerInterface
{
    public function __construct(
        private EventDispatcherInterface $eventDispatcher,
        private LoggerInterface $logger,
    ) {
    }

    public function supports(QueueContextInterface $context): bool
    {
        return $context instanceof JoinQueueContext;
    }

    public function handle(QueueContextInterface $context): void
    {
        if (!$context instanceof JoinQueueContext) {
            return;
        }

        $queuedUser = $context->getQueuedUser();

        $this->logger->debug('Dispatching queued user created event for {contextId} {contextType}', [
            'contextId' => $context->getId(),
            'contextType' => $context->getType()->value,
        ]);

        $this->eventDispatcher->dispatch(new QueuedUserCreatedEvent($queuedUser));
    }
}

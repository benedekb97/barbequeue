<?php

declare(strict_types=1);

namespace App\Service\Queue\Handler;

use App\Event\QueuedUser\QueuedUserRemovedEvent;
use App\Service\Queue\Context\ContextType;
use App\Service\Queue\Context\QueueContextInterface;
use App\Service\Queue\Leave\Handler\LeaveQueueHandlerInterface;
use App\Service\Queue\Leave\LeaveQueueContext;
use App\Service\Queue\Pop\Handler\PopQueueHandlerInterface;
use App\Service\Queue\Pop\PopQueueContext;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem(priority: 5_600)]
readonly class DispatchQueuedUserRemovedEventHandler implements LeaveQueueHandlerInterface, PopQueueHandlerInterface
{
    public function __construct(
        private EventDispatcherInterface $eventDispatcher,
        private LoggerInterface $logger,
    ) {
    }

    public function supports(QueueContextInterface $context): bool
    {
        return in_array($context->getType(), [ContextType::POP, ContextType::LEAVE]);
    }

    public function handle(QueueContextInterface $context): void
    {
        if (!$context instanceof LeaveQueueContext && !$context instanceof PopQueueContext) {
            return;
        }

        $this->logger->debug('Dispatching queued user removed event for {queue} {contextId} {contextType}', [
            'queue' => $context->getQueue()->getId(),
            'contextId' => $context->getId(),
            'contextType' => ($contextType = $context->getType())->value,
        ]);

        $this->eventDispatcher->dispatch(new QueuedUserRemovedEvent(
            $context->getQueuedUser(),
            $context->getQueue(),
            ContextType::POP === $contextType,
        ));
    }
}

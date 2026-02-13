<?php

declare(strict_types=1);

namespace App\Service\Queue\Handler;

use App\Event\HomeTabUpdatedEvent;
use App\Service\Queue\Context\QueueContextInterface;
use App\Service\Queue\Edit\EditQueueContext;
use App\Service\Queue\Edit\Handler\EditQueueHandlerInterface;
use App\Service\Queue\Pop\Handler\PopQueueHandlerInterface;
use App\Service\Queue\Pop\PopQueueContext;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem(priority: 0)]
readonly class DispatchHomeTabUpdatedEventHandler implements EditQueueHandlerInterface, PopQueueHandlerInterface
{
    public function __construct(
        private LoggerInterface $logger,
        private EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function handle(QueueContextInterface $context): void
    {
        $this->logger->debug('Dispatching home tab updated event for {contextId} {contextType}', [
            'contextId' => $context->getId(),
            'contextType' => $context->getType(),
        ]);

        $this->eventDispatcher->dispatch(new HomeTabUpdatedEvent(
            $context->getUserId(),
            $context->getTeamId(),
        ));
    }

    public function supports(QueueContextInterface $context): bool
    {
        return $context instanceof EditQueueContext || $context instanceof PopQueueContext;
    }
}

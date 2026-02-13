<?php

declare(strict_types=1);

namespace App\Service\Queue\Leave\Handler;

use App\Entity\Deployment;
use App\Entity\Repository;
use App\Event\Deployment\DeploymentCancelledEvent;
use App\Event\Deployment\DeploymentCompletedEvent;
use App\Service\Queue\Context\QueueContextInterface;
use App\Service\Queue\Leave\LeaveQueueContext;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem(priority: 5_550)]
readonly class DispatchDeploymentEventHandler implements LeaveQueueHandlerInterface
{
    public function __construct(
        private LoggerInterface $logger,
        private EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function supports(QueueContextInterface $context): bool
    {
        return $context instanceof LeaveQueueContext && $context->getQueuedUser() instanceof Deployment;
    }

    public function handle(QueueContextInterface $context): void
    {
        if (!$context instanceof LeaveQueueContext) {
            return;
        }

        $deployment = $context->getQueuedUser();

        if (!$deployment instanceof Deployment) {
            return;
        }

        /** @var Repository $repository */
        $repository = $context->getRepository();

        $event = $deployment->isActive()
            ? new DeploymentCompletedEvent($deployment, $context->getWorkspace(), $repository)
            : new DeploymentCancelledEvent($deployment, $context->getWorkspace(), $repository);

        $this->logger->debug('Dispatching {event} for removing deployment on {queue} for {contextId} {contextType}', [
            'event' => $event::class,
            'queue' => $context->getQueue()->getId(),
            'contextId' => $context->getId(),
            'contextType' => $context->getType(),
        ]);

        $this->eventDispatcher->dispatch($event);
    }
}

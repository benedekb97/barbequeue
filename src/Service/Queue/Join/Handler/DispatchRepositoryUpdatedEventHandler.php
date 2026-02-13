<?php

declare(strict_types=1);

namespace App\Service\Queue\Join\Handler;

use App\Entity\Deployment;
use App\Event\Repository\RepositoryUpdatedEvent;
use App\Service\Queue\Context\QueueContextInterface;
use App\Service\Queue\Join\JoinQueueContext;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem(priority: 2_000)]
readonly class DispatchRepositoryUpdatedEventHandler implements JoinQueueHandlerInterface
{
    public function __construct(
        private LoggerInterface $logger,
        private EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function supports(QueueContextInterface $context): bool
    {
        return $context instanceof JoinQueueContext && $context->getQueuedUser() instanceof Deployment;
    }

    public function handle(QueueContextInterface $context): void
    {
        if (!$context instanceof JoinQueueContext) {
            return;
        }

        /** @var Deployment $deployment */
        $deployment = $context->getQueuedUser();

        $this->logger->debug('Dispatching repository updated event for {repository} {contextId} {contextType}', [
            'repository' => $deployment->getRepository()?->getId(),
            'contextId' => $context->getId(),
            'contextType' => $context->getType()->value,
        ]);

        $this->eventDispatcher->dispatch(new RepositoryUpdatedEvent($deployment->getRepository()));
    }
}

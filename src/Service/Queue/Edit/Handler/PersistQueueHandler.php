<?php

declare(strict_types=1);

namespace App\Service\Queue\Edit\Handler;

use App\Service\Queue\Context\ContextType;
use App\Service\Queue\Context\QueueContextInterface;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem(priority: 1_000)]
readonly class PersistQueueHandler implements EditQueueHandlerInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger,
    ) {
    }

    public function supports(QueueContextInterface $context): bool
    {
        return ContextType::EDIT === $context->getType();
    }

    public function handle(QueueContextInterface $context): void
    {
        $this->logger->debug('Persisting {queue} for {contextId} {contextType}', [
            'queue' => $context->getQueue()->getId(),
            'contextId' => $context->getId(),
            'contextType' => $context->getType()->value,
        ]);

        $this->entityManager->persist($context->getQueue());
    }
}

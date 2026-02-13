<?php

declare(strict_types=1);

namespace App\Service\Queue\Handler;

use App\Service\Queue\Context\QueueContextInterface;
use App\Service\Queue\Edit\Handler\EditQueueHandlerInterface;
use App\Service\Queue\Join\Handler\JoinQueueHandlerInterface;
use App\Service\Queue\Leave\Handler\LeaveQueueHandlerInterface;
use App\Service\Queue\Pop\Handler\PopQueueHandlerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem(priority: 100)]
readonly class FlushEntitiesHandler implements JoinQueueHandlerInterface, LeaveQueueHandlerInterface, EditQueueHandlerInterface, PopQueueHandlerInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger,
    ) {
    }

    public function supports(QueueContextInterface $context): bool
    {
        return true;
    }

    public function handle(QueueContextInterface $context): void
    {
        $this->logger->debug('Flushing entities for {contextId} {contextType}', [
            'contextId' => $context->getId(),
            'contextType' => $context->getType(),
        ]);

        $this->entityManager->flush();
    }
}

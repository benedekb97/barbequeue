<?php

declare(strict_types=1);

namespace App\Service\Queue\Handler;

use App\Entity\Deployment;
use App\Entity\Repository;
use App\Service\Queue\Context\QueueContextInterface;
use App\Service\Queue\Leave\Handler\LeaveQueueHandlerInterface;
use App\Service\Queue\Leave\LeaveQueueContext;
use App\Service\Queue\Pop\Handler\PopQueueHandlerInterface;
use App\Service\Queue\Pop\PopQueueContext;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem(priority: 5_700)]
readonly class RemoveDeploymentFromRepositoryHandler implements LeaveQueueHandlerInterface, PopQueueHandlerInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger,
    ) {
    }

    public function supports(QueueContextInterface $context): bool
    {
        return ($context instanceof LeaveQueueContext || $context instanceof PopQueueContext)
            && $context->getQueuedUser() instanceof Deployment;
    }

    public function handle(QueueContextInterface $context): void
    {
        if (!$context instanceof LeaveQueueContext && !$context instanceof PopQueueContext) {
            return;
        }

        /** @var Deployment $deployment */
        $deployment = $context->getQueuedUser();

        /** @var Repository $repository */
        $repository = $deployment->getRepository();

        $context->setRepository($repository);

        $this->logger->debug('Removing {deployment} from {repository} {contextId} {contextType}', [
            'deployment' => $deployment->getId(),
            'repository' => $repository->getId(),
            'contextId' => $context->getId(),
            'contextType' => $context->getType()->value,
        ]);

        $repository->removeDeployment($deployment);

        $this->entityManager->persist($repository);
    }
}

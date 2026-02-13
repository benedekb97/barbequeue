<?php

declare(strict_types=1);

namespace App\Service\Queue\Join\Handler;

use App\Entity\Deployment;
use App\Entity\Repository;
use App\Service\Queue\Context\QueueContextInterface;
use App\Service\Queue\Join\JoinQueueContext;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem(priority: 4_200)]
readonly class SetDeploymentInformationHandler implements JoinQueueHandlerInterface
{
    public function __construct(
        private LoggerInterface $logger,
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

        $deployment = $context->getQueuedUser();

        if (!$deployment instanceof Deployment) {
            return;
        }

        if (empty($description = $context->getDeploymentDescription())) {
            return;
        }

        if (empty($link = $context->getDeploymentLink())) {
            return;
        }

        $repository = $context->getRepository();

        if (!$repository instanceof Repository) {
            return;
        }

        $this->logger->debug('Setting deployment information for {queue} {contextId} {contextType}', [
            'queue' => $deployment->getQueue()?->getId(),
            'contextId' => $context->getId(),
            'contextType' => $context->getType()->value,
        ]);

        $deployment->setDescription($description)
            ->setLink($link)
            ->setRepository($repository);

        $repository->addDeployment($deployment);
    }
}

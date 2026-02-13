<?php

declare(strict_types=1);

namespace App\Service\Queue\Pop\Handler;

use App\Entity\QueuedUser;
use App\Service\Queue\Context\ContextType;
use App\Service\Queue\Context\QueueContextInterface;
use App\Service\Queue\Exception\PopQueueInformationRequiredException;
use App\Service\Queue\Pop\PopQueueContext;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem(priority: 5_900)]
readonly class ResolveQueuedUserHandler implements PopQueueHandlerInterface
{
    public function __construct(
        private LoggerInterface $logger,
    ) {
    }

    public function supports(QueueContextInterface $context): bool
    {
        return ContextType::POP === $context->getType();
    }

    /** @throws PopQueueInformationRequiredException */
    public function handle(QueueContextInterface $context): void
    {
        if (!$context instanceof PopQueueContext) {
            return;
        }

        $this->logger->debug('Resolving queued user on {queue} for {contextId} {contextType}', [
            'queue' => ($queue = $context->getQueue())->getId(),
            'contextId' => $context->getId(),
            'contextType' => $context->getType()->value,
        ]);

        if (($queuedUsers = $queue->getQueuedUsers())->count() === 1) {
            /** @var QueuedUser $queuedUser */
            $queuedUser = $queuedUsers->first();

            $this->logger->debug('{queue} only has one queued user for {contextId} {contextType}', [
                'queue' => $queue->getId(),
                'contextId' => $context->getId(),
                'contextType' => $context->getType()->value,
            ]);

            $context->setQueuedUser($queuedUser);

            return;
        }

        if (($queuedUserId = $context->getQueuedUserId()) === null) {
            $this->logger->debug('Queued user ID not provided, opening modal {contextId} {contextType}', [
                'contextId' => $context->getId(),
                'contextType' => $context->getType()->value,
            ]);

            throw new PopQueueInformationRequiredException($queue);
        }

        $queuedUser = $queuedUsers->filter(function (QueuedUser $queuedUser) use ($queuedUserId) {
            return $queuedUser->getId() === $queuedUserId;
        })->first();

        if (false === $queuedUser) {
            $this->logger->warning('Provided {queuedUserId} does not exist on {queue} for {contextId} {contextType}', [
                'queuedUserId' => $queuedUserId,
                'queue' => $queue->getId(),
                'contextId' => $context->getId(),
                'contextType' => $context->getType()->value,
            ]);

            throw new PopQueueInformationRequiredException($queue);
        }

        $context->setQueuedUser($queuedUser);
    }
}

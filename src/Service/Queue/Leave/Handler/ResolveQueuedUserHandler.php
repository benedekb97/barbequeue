<?php

declare(strict_types=1);

namespace App\Service\Queue\Leave\Handler;

use App\Entity\QueuedUser;
use App\Service\Queue\Context\ContextType;
use App\Service\Queue\Context\QueueContextInterface;
use App\Service\Queue\Exception\LeaveQueueInformationRequiredException;
use App\Service\Queue\Leave\LeaveQueueContext;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem(priority: 5_900)]
readonly class ResolveQueuedUserHandler implements LeaveQueueHandlerInterface
{
    public function __construct(
        private LoggerInterface $logger,
    ) {
    }

    public function supports(QueueContextInterface $context): bool
    {
        return ContextType::LEAVE === $context->getType();
    }

    /** @throws LeaveQueueInformationRequiredException */
    public function handle(QueueContextInterface $context): void
    {
        if (!$context instanceof LeaveQueueContext) {
            return;
        }

        $queuedUsers = ($queue = $context->getQueue())->getQueuedUsersByUserId($context->getUserId());

        if (1 === $queuedUsers->count()) {
            /** @var QueuedUser $queuedUser */
            $queuedUser = $queuedUsers->first();

            $context->setQueuedUser($queuedUser);

            $this->logger->debug('User only has one queued user on {queue} {contextId} {contextType}', [
                'queue' => $queue->getName(),
                'contextId' => $context->getId(),
                'contextType' => $context->getType()->value,
            ]);

            return;
        }

        if (($queuedUserId = $context->getQueuedUserId()) === null) {
            $this->logger->debug('Queued user ID not provided, opening modal {contextId} {contextType}', [
                'contextId' => $context->getId(),
                'contextType' => $context->getType()->value,
            ]);

            throw new LeaveQueueInformationRequiredException($queue);
        }

        $queuedUser = $queuedUsers->filter(function (QueuedUser $queuedUser) use ($queuedUserId) {
            return $queuedUser->getId() === $queuedUserId;
        })->first();

        if (false === $queuedUser) {
            $this->logger->warning('Provided {queuedUserId} does not exist on {queue} {contextId} {contextType}', [
                'queuedUserId' => $queuedUserId,
                'queue' => $queue->getName(),
                'contextId' => $context->getId(),
                'contextType' => $context->getType()->value,
            ]);

            throw new LeaveQueueInformationRequiredException($queue);
        }

        $context->setQueuedUser($queuedUser);
    }
}

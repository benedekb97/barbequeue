<?php

declare(strict_types=1);

namespace App\Service\Queue\Handler;

use App\Resolver\UserResolver;
use App\Service\Queue\Context\QueueContextInterface;
use App\Service\Queue\Join\Handler\JoinQueueHandlerInterface;
use App\Service\Queue\Join\JoinQueueContext;
use App\Service\Queue\Leave\Handler\LeaveQueueHandlerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem(priority: 9_000)]
readonly class ResolveUserHandler implements JoinQueueHandlerInterface, LeaveQueueHandlerInterface
{
    public function __construct(
        private UserResolver $userResolver,
        private LoggerInterface $logger,
    ) {
    }

    public function supports(QueueContextInterface $context): bool
    {
        return !$context->hasUser();
    }

    public function handle(QueueContextInterface $context): void
    {
        $this->logger->debug('Resolving user by {userId} and {workspaceId} for {contextId} {contextType}', [
            'userId' => $userId = $context->getUserId(),
            'workspaceId' => ($workspace = $context->getWorkspace())->getId(),
            'contextId' => $context->getId(),
            'contextType' => $context->getType()->value,
        ]);

        $userName = $context instanceof JoinQueueContext ? $context->getUserName() : null;

        $context->setUser($this->userResolver->resolve($userId, $workspace, $userName));
    }
}

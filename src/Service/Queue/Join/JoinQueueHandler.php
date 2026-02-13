<?php

declare(strict_types=1);

namespace App\Service\Queue\Join;

use App\Service\Queue\Exception\DeploymentInformationRequiredException;
use App\Service\Queue\Exception\InvalidDeploymentUrlException;
use App\Service\Queue\Exception\QueueNotFoundException;
use App\Service\Queue\Exception\UnableToJoinQueueException;
use App\Service\Queue\Join\Handler\JoinQueueHandlerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

readonly class JoinQueueHandler
{
    public function __construct(
        /** @var JoinQueueHandlerInterface[] */
        #[AutowireIterator(JoinQueueHandlerInterface::class)]
        private iterable $handlers,
    ) {
    }

    /**
     * @throws QueueNotFoundException
     * @throws UnableToJoinQueueException
     * @throws DeploymentInformationRequiredException
     * @throws InvalidDeploymentUrlException
     */
    public function handle(JoinQueueContext $context): void
    {
        foreach ($this->handlers as $handler) {
            if ($handler->supports($context)) {
                $handler->handle($context);
            }
        }
    }
}

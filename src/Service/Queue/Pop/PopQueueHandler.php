<?php

declare(strict_types=1);

namespace App\Service\Queue\Pop;

use App\Service\Queue\Exception\PopQueueInformationRequiredException;
use App\Service\Queue\Exception\QueueNotFoundException;
use App\Service\Queue\Pop\Handler\PopQueueHandlerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

readonly class PopQueueHandler
{
    public function __construct(
        /** @var PopQueueHandlerInterface[] $handlers */
        #[AutowireIterator(PopQueueHandlerInterface::class)]
        private iterable $handlers,
    ) {
    }

    /**
     * @throws PopQueueInformationRequiredException
     * @throws QueueNotFoundException
     */
    public function handle(PopQueueContext $context): void
    {
        foreach ($this->handlers as $handler) {
            if ($handler->supports($context)) {
                $handler->handle($context);
            }
        }
    }
}

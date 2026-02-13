<?php

declare(strict_types=1);

namespace App\Service\Queue\Leave;

use App\Service\Queue\Exception\LeaveQueueInformationRequiredException;
use App\Service\Queue\Exception\QueueNotFoundException;
use App\Service\Queue\Exception\UnableToLeaveQueueException;
use App\Service\Queue\Leave\Handler\LeaveQueueHandlerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

readonly class LeaveQueueHandler
{
    public function __construct(
        /** @var LeaveQueueHandlerInterface[] $handlers */
        #[AutowireIterator(LeaveQueueHandlerInterface::class)]
        private iterable $handlers,
    ) {
    }

    /**
     * @throws QueueNotFoundException
     * @throws UnableToLeaveQueueException
     * @throws LeaveQueueInformationRequiredException
     */
    public function handle(LeaveQueueContext $context): void
    {
        foreach ($this->handlers as $handler) {
            if ($handler->supports($context)) {
                $handler->handle($context);
            }
        }
    }
}

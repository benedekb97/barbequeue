<?php

declare(strict_types=1);

namespace App\Service\Queue\Leave\Handler;

use App\Service\Queue\Context\QueueContextInterface;
use App\Service\Queue\Exception\LeaveQueueInformationRequiredException;
use App\Service\Queue\Exception\QueueNotFoundException;
use App\Service\Queue\Exception\UnableToLeaveQueueException;
use App\Service\Queue\Handler\QueueHandlerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag]
interface LeaveQueueHandlerInterface extends QueueHandlerInterface
{
    /**
     * @throws UnableToLeaveQueueException
     * @throws LeaveQueueInformationRequiredException
     * @throws QueueNotFoundException
     */
    public function handle(QueueContextInterface $context): void;
}

<?php

declare(strict_types=1);

namespace App\Service\Queue\Pop\Handler;

use App\Service\Queue\Context\QueueContextInterface;
use App\Service\Queue\Exception\PopQueueInformationRequiredException;
use App\Service\Queue\Exception\QueueNotFoundException;
use App\Service\Queue\Handler\QueueHandlerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag]
interface PopQueueHandlerInterface extends QueueHandlerInterface
{
    /**
     * @throws PopQueueInformationRequiredException
     * @throws QueueNotFoundException
     */
    public function handle(QueueContextInterface $context): void;
}

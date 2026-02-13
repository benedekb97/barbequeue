<?php

declare(strict_types=1);

namespace App\Service\Queue\Join\Handler;

use App\Service\Queue\Context\QueueContextInterface;
use App\Service\Queue\Exception\DeploymentInformationRequiredException;
use App\Service\Queue\Exception\InvalidDeploymentUrlException;
use App\Service\Queue\Exception\QueueNotFoundException;
use App\Service\Queue\Exception\UnableToJoinQueueException;
use App\Service\Queue\Handler\QueueHandlerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag]
interface JoinQueueHandlerInterface extends QueueHandlerInterface
{
    /**
     * @throws DeploymentInformationRequiredException
     * @throws UnableToJoinQueueException
     * @throws QueueNotFoundException
     * @throws InvalidDeploymentUrlException
     */
    public function handle(QueueContextInterface $context): void;
}

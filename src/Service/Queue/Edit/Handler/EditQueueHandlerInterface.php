<?php

declare(strict_types=1);

namespace App\Service\Queue\Edit\Handler;

use App\Service\Queue\Context\QueueContextInterface;
use App\Service\Queue\Handler\QueueHandlerInterface;
use App\Service\Repository\Exception\RepositoryNotFoundException;
use Doctrine\ORM\EntityNotFoundException;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag]
interface EditQueueHandlerInterface extends QueueHandlerInterface
{
    /**
     * @throws EntityNotFoundException
     * @throws RepositoryNotFoundException
     */
    public function handle(QueueContextInterface $context): void;
}

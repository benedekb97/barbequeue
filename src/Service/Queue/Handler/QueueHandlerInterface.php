<?php

declare(strict_types=1);

namespace App\Service\Queue\Handler;

use App\Service\Queue\Context\QueueContextInterface;

interface QueueHandlerInterface
{
    public function supports(QueueContextInterface $context): bool;

    public function handle(QueueContextInterface $context): void;
}

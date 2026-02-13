<?php

declare(strict_types=1);

namespace App\Slack\Surface\Factory\Resolver\Queue;

use App\Slack\Surface\Component\ModalArgument;
use App\Slack\Surface\Factory\Resolver\DefaultValueResolverInterface;

class MaximumEntriesDefaultValueResolver extends AbstractQueueDefaultValueResolver implements DefaultValueResolverInterface
{
    public function getSupportedArgument(): ModalArgument
    {
        return ModalArgument::QUEUE_MAXIMUM_ENTRIES_PER_USER;
    }

    public function resolveString(): ?string
    {
        if (null === $this->queue) {
            return null;
        }

        if (($maxEntries = $this->queue->getMaximumEntriesPerUser()) === null) {
            return null;
        }

        return (string) $maxEntries;
    }

    public function resolveArray(): ?array
    {
        return null;
    }
}

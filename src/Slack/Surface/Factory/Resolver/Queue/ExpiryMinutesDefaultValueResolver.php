<?php

declare(strict_types=1);

namespace App\Slack\Surface\Factory\Resolver\Queue;

use App\Slack\Surface\Component\ModalArgument;
use App\Slack\Surface\Factory\Resolver\DefaultValueResolverInterface;

class ExpiryMinutesDefaultValueResolver extends AbstractQueueDefaultValueResolver implements DefaultValueResolverInterface
{
    public function getSupportedArgument(): ModalArgument
    {
        return ModalArgument::QUEUE_EXPIRY_MINUTES;
    }

    public function resolveString(): ?string
    {
        if (null === $this->queue) {
            return null;
        }

        if (($expiry = $this->queue->getExpiryMinutes()) === null) {
            return null;
        }

        return (string) $expiry;
    }

    public function resolveArray(): ?array
    {
        return null;
    }
}

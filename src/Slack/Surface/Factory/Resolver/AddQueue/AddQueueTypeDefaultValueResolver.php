<?php

declare(strict_types=1);

namespace App\Slack\Surface\Factory\Resolver\AddQueue;

use App\Enum\Queue;
use App\Slack\Surface\Component\ModalArgument;
use App\Slack\Surface\Factory\Resolver\DefaultValueResolverInterface;

class AddQueueTypeDefaultValueResolver implements DefaultValueResolverInterface
{
    private ?Queue $queue = null;

    public function setQueue(?Queue $queue): void
    {
        $this->queue = $queue;
    }

    public function getSupportedArgument(): ModalArgument
    {
        return ModalArgument::QUEUE_TYPE;
    }

    public function resolveString(): ?string
    {
        return null;
    }

    public function resolveArray(): ?array
    {
        if (null === $this->queue) {
            return null;
        }

        return [
            'text' => [
                'type' => 'plain_text',
                'text' => $this->queue->getName(),
            ],
            'value' => $this->queue->value,
        ];
    }
}

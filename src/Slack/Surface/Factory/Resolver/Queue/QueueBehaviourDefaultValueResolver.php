<?php

declare(strict_types=1);

namespace App\Slack\Surface\Factory\Resolver\Queue;

use App\Entity\DeploymentQueue;
use App\Slack\Surface\Component\ModalArgument;
use App\Slack\Surface\Factory\Resolver\DefaultValueResolverInterface;

class QueueBehaviourDefaultValueResolver extends AbstractQueueDefaultValueResolver implements DefaultValueResolverInterface
{
    public function getSupportedArgument(): ModalArgument
    {
        return ModalArgument::QUEUE_BEHAVIOUR;
    }

    public function resolveString(): ?string
    {
        return null;
    }

    public function resolveArray(): ?array
    {
        if (!$this->queue instanceof DeploymentQueue) {
            return null;
        }

        return [
            'text' => [
                'type' => 'plain_text',
                'text' => $this->queue->getBehaviour()->getName(),
            ],
            'value' => $this->queue->getBehaviour()->value,
        ];
    }
}

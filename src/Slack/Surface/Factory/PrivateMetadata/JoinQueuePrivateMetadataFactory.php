<?php

declare(strict_types=1);

namespace App\Slack\Surface\Factory\PrivateMetadata;

use App\Entity\DeploymentQueue;
use App\Entity\Queue;
use App\Slack\Interaction\Interaction;
use App\Slack\Surface\Component\ModalArgument;
use App\Slack\Surface\Factory\PrivateMetadata\Exception\JsonEncodingException;

class JoinQueuePrivateMetadataFactory implements PrivateMetadataFactoryInterface
{
    private ?Queue $queue = null;

    private ?string $responseUrl = null;

    public function setQueue(?Queue $queue): static
    {
        $this->queue = $queue;

        return $this;
    }

    public function setResponseUrl(?string $responseUrl): static
    {
        $this->responseUrl = $responseUrl;

        return $this;
    }

    public function create(): string
    {
        if (null === $this->queue) {
            throw new JsonEncodingException('Could not encode private metadata: missing queue');
        }

        if (!$this->queue instanceof DeploymentQueue) {
            throw new JsonEncodingException('Could not encode private metadata: queue not deployment queue');
        }

        $metadata = json_encode(array_filter([
            'action' => Interaction::JOIN_QUEUE_DEPLOYMENT->value,
            ModalArgument::JOIN_QUEUE_NAME->value => $this->queue->getName(),
            'response_url' => $this->responseUrl,
        ]));

        if (false === $metadata) {
            throw new JsonEncodingException('Could not encode private metadata.');
        }

        return $metadata;
    }
}

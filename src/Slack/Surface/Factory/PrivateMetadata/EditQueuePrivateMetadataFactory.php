<?php

declare(strict_types=1);

namespace App\Slack\Surface\Factory\PrivateMetadata;

use App\Entity\DeploymentQueue;
use App\Entity\Queue;
use App\Slack\Interaction\Interaction;
use App\Slack\Surface\Factory\PrivateMetadata\Exception\JsonEncodingException;

class EditQueuePrivateMetadataFactory implements PrivateMetadataFactoryInterface
{
    private ?Queue $queue = null;

    private ?string $responseUrl = null;

    public function create(): string
    {
        if (!$this->queue instanceof Queue) {
            throw new JsonEncodingException('Could not encode private metadata: missing queue');
        }

        $interaction = match (true) {
            $this->queue instanceof DeploymentQueue => Interaction::EDIT_QUEUE_DEPLOYMENT,
            default => Interaction::EDIT_QUEUE,
        };

        $metadata = json_encode(array_filter([
            'queue' => $this->queue->getId(),
            'action' => $interaction->value,
            'response_url' => $this->responseUrl,
        ]));

        if (false === $metadata) {
            throw new JsonEncodingException('Could not encode private metadata');
        }

        return $metadata;
    }

    public function setQueue(Queue $queue): static
    {
        $this->queue = $queue;

        return $this;
    }

    public function setResponseUrl(string $url): static
    {
        $this->responseUrl = $url;

        return $this;
    }
}

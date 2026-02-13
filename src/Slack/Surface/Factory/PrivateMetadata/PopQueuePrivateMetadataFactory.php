<?php

declare(strict_types=1);

namespace App\Slack\Surface\Factory\PrivateMetadata;

use App\Entity\Queue;
use App\Slack\Interaction\Interaction;
use App\Slack\Surface\Factory\PrivateMetadata\Exception\JsonEncodingException;

class PopQueuePrivateMetadataFactory implements PrivateMetadataFactoryInterface
{
    private ?Queue $queue = null;

    private ?string $responseUrl = null;

    public function setQueue(?Queue $queue): static
    {
        $this->queue = $queue;

        return $this;
    }

    public function setResponseUrl(?string $url): static
    {
        $this->responseUrl = $url;

        return $this;
    }

    public function create(): string
    {
        if (null === $this->queue) {
            throw new JsonEncodingException('Unable to encode private metadata - missing queue');
        }

        $metadata = json_encode(array_filter([
            'queue' => $this->queue->getName(),
            'action' => Interaction::POP_QUEUE_ACTION->value,
            'response_url' => $this->responseUrl,
        ]));

        if (false === $metadata) {
            throw new JsonEncodingException('Unable to encode private metadata');
        }

        return $metadata;
    }
}

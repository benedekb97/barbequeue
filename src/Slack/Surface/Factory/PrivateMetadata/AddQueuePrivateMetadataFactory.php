<?php

declare(strict_types=1);

namespace App\Slack\Surface\Factory\PrivateMetadata;

use App\Enum\Queue;
use App\Slack\Interaction\Interaction;
use App\Slack\Surface\Factory\PrivateMetadata\Exception\JsonEncodingException;

class AddQueuePrivateMetadataFactory implements PrivateMetadataFactoryInterface
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
        $queue = $this->queue ?? Queue::SIMPLE;

        $action = match ($queue) {
            Queue::SIMPLE => Interaction::ADD_SIMPLE_QUEUE,
            Queue::DEPLOYMENT => Interaction::ADD_DEPLOYMENT_QUEUE,
        };

        $metadata = json_encode(array_filter([
            'action' => $action->value,
            'response_url' => $this->responseUrl,
        ]));

        if (false === $metadata) {
            throw new JsonEncodingException();
        }

        return $metadata;
    }
}

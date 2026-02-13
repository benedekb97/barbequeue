<?php

declare(strict_types=1);

namespace App\Slack\Surface\Factory\PrivateMetadata;

use App\Slack\Interaction\Interaction;
use App\Slack\Surface\Factory\PrivateMetadata\Exception\JsonEncodingException;

class ConfigurationPrivateMetadataFactory implements PrivateMetadataFactoryInterface
{
    private ?string $responseUrl = null;

    public function setResponseUrl(?string $responseUrl): void
    {
        $this->responseUrl = $responseUrl;
    }

    public function create(): string
    {
        $metadata = json_encode(array_filter([
            'action' => Interaction::SAVE_CONFIGURATION->value,
            'response_url' => $this->responseUrl,
        ]));

        if (false === $metadata) {
            throw new JsonEncodingException('Could not encode private metadata for configuration modal!');
        }

        return $metadata;
    }
}

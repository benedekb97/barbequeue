<?php

declare(strict_types=1);

namespace App\Slack\Surface\Factory\PrivateMetadata;

use App\Entity\Repository;
use App\Slack\Interaction\Interaction;
use App\Slack\Surface\Factory\PrivateMetadata\Exception\JsonEncodingException;

class EditRepositoryPrivateMetadataFactory implements PrivateMetadataFactoryInterface
{
    private ?Repository $repository = null;

    private ?string $responseUrl = null;

    public function create(): string
    {
        if (!$this->repository instanceof Repository) {
            throw new JsonEncodingException('Could not encode private metadata: missing repository');
        }

        $metadata = json_encode(array_filter([
            'repository_id' => $this->repository->getId(),
            'action' => Interaction::EDIT_REPOSITORY->value,
            'response_url' => $this->responseUrl,
        ]));

        if (false === $metadata) {
            throw new JsonEncodingException('Could not encode private metadata');
        }

        return $metadata;
    }

    public function setRepository(Repository $repository): static
    {
        $this->repository = $repository;

        return $this;
    }

    public function setResponseUrl(?string $responseUrl): static
    {
        $this->responseUrl = $responseUrl;

        return $this;
    }
}

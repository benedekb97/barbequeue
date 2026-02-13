<?php

declare(strict_types=1);

namespace App\Slack\Surface\Factory;

use App\Slack\Common\Component\UserTriggeredInteractionInterface;
use App\Slack\Surface\Component\Modal;
use App\Slack\Surface\Component\ModalSurface;
use App\Slack\Surface\Factory\Inputs\ModalInputsFactoryInterface;
use App\Slack\Surface\Factory\PrivateMetadata\Exception\JsonEncodingException;
use App\Slack\Surface\Factory\PrivateMetadata\PrivateMetadataFactoryInterface;
use Psr\Log\LoggerInterface;

class InputModalFactory
{
    private ?ModalInputsFactoryInterface $inputsFactory = null;

    private ?PrivateMetadataFactoryInterface $privateMetadataFactory = null;

    public function __construct(
        private readonly LoggerInterface $logger,
    ) {
    }

    public function create(
        UserTriggeredInteractionInterface $interaction,
        Modal $modal,
        ?string $close = null,
        ?string $submit = null,
    ): ?ModalSurface {
        $this->logger->debug('Creating modal '.$modal->value);

        if (!$this->inputsFactory instanceof ModalInputsFactoryInterface) {
            $this->logger->error(
                sprintf('Failed to create %s modal. Inputs factory not set.', $modal->value)
            );

            return null;
        }

        try {
            $privateMetadata = $this->privateMetadataFactory?->create();
        } catch (JsonEncodingException $exception) {
            $this->logger->debug('Could not encode provided private metadata for modal '.$modal->value);
            $this->logger->error($exception->getMessage());
        }

        return new ModalSurface(
            $interaction->getTriggerId(),
            $modal->getTitle(),
            $this->inputsFactory->create($modal),
            $close,
            $submit,
            privateMetadata: $privateMetadata ?? null,
        );
    }

    public function setInputsFactory(ModalInputsFactoryInterface $inputsFactory): InputModalFactory
    {
        $this->inputsFactory = $inputsFactory;

        return $this;
    }

    public function setPrivateMetadataFactory(PrivateMetadataFactoryInterface $privateMetadataFactory): InputModalFactory
    {
        $this->privateMetadataFactory = $privateMetadataFactory;

        return $this;
    }
}

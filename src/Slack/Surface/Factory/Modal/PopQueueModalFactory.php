<?php

declare(strict_types=1);

namespace App\Slack\Surface\Factory\Modal;

use App\Entity\Queue;
use App\Slack\Common\Component\UserTriggeredInteractionInterface;
use App\Slack\Surface\Component\Modal;
use App\Slack\Surface\Component\ModalSurface;
use App\Slack\Surface\Factory\InputModalFactory;
use App\Slack\Surface\Factory\Inputs\ModalInputsFactory;
use App\Slack\Surface\Factory\PrivateMetadata\PopQueuePrivateMetadataFactory;
use App\Slack\Surface\Factory\Resolver\PopQueue\PopQueueOptionsResolver;

readonly class PopQueueModalFactory
{
    public function __construct(
        private InputModalFactory $inputModalFactory,
        private ModalInputsFactory $modalInputsFactory,
        private PopQueuePrivateMetadataFactory $privateMetadataFactory,
        private PopQueueOptionsResolver $optionsResolver,
    ) {
    }

    public function create(Queue $queue, UserTriggeredInteractionInterface $interaction): ?ModalSurface
    {
        $this->optionsResolver->setQueue($queue);
        $this->privateMetadataFactory->setQueue($queue)
            ->setResponseUrl($interaction->getResponseUrl());

        $this->modalInputsFactory
            ->setOptionsResolvers([$this->optionsResolver])
            ->setDefaultValueResolvers([]);

        return $this->inputModalFactory
            ->setInputsFactory($this->modalInputsFactory)
            ->setPrivateMetadataFactory($this->privateMetadataFactory)
            ->create(
                $interaction,
                Modal::POP_QUEUE,
                'Cancel',
                'Remove',
            );
    }
}

<?php

declare(strict_types=1);

namespace App\Slack\Surface\Factory\Modal;

use App\Entity\DeploymentQueue;
use App\Entity\Queue;
use App\Slack\Common\Component\UserTriggeredInteractionInterface;
use App\Slack\Surface\Component\Modal;
use App\Slack\Surface\Component\ModalSurface;
use App\Slack\Surface\Factory\InputModalFactory;
use App\Slack\Surface\Factory\Inputs\ModalInputsFactory;
use App\Slack\Surface\Factory\PrivateMetadata\JoinQueuePrivateMetadataFactory;
use App\Slack\Surface\Factory\Resolver\JoinQueue\JoinQueueRepositoryOptionsResolver;

readonly class JoinQueueModalFactory
{
    public function __construct(
        private InputModalFactory $inputModalFactory,
        private ModalInputsFactory $modalInputsFactory,
        private JoinQueuePrivateMetadataFactory $joinQueuePrivateMetadataFactory,
        private JoinQueueRepositoryOptionsResolver $joinQueueRepositoryOptionsResolver,
    ) {
    }

    public function create(Queue $queue, UserTriggeredInteractionInterface $interaction): ?ModalSurface
    {
        if (!$queue instanceof DeploymentQueue) {
            return null;
        }

        $this->joinQueuePrivateMetadataFactory->setQueue($queue)
            ->setResponseUrl($interaction->getResponseUrl());

        $this->joinQueueRepositoryOptionsResolver->setQueue($queue);

        $this->modalInputsFactory->setDefaultValueResolvers([])
            ->setOptionsResolvers([$this->joinQueueRepositoryOptionsResolver]);

        return $this->inputModalFactory
            ->setInputsFactory($this->modalInputsFactory)
            ->setPrivateMetadataFactory($this->joinQueuePrivateMetadataFactory)
            ->create(
                $interaction,
                Modal::JOIN_QUEUE_DEPLOYMENT,
                'Cancel',
                'Join',
            );
    }
}

<?php

declare(strict_types=1);

namespace App\Slack\Surface\Factory\Modal;

use App\Entity\Queue;
use App\Slack\Common\Component\UserTriggeredInteractionInterface;
use App\Slack\Surface\Component\Modal;
use App\Slack\Surface\Component\ModalSurface;
use App\Slack\Surface\Factory\InputModalFactory;
use App\Slack\Surface\Factory\Inputs\ModalInputsFactory;
use App\Slack\Surface\Factory\PrivateMetadata\LeaveQueuePrivateMetadataFactory;
use App\Slack\Surface\Factory\Resolver\LeaveQueue\LeaveQueueOptionsResolver;

readonly class LeaveQueueModalFactory
{
    public function __construct(
        private InputModalFactory $inputModalFactory,
        private ModalInputsFactory $modalInputsFactory,
        private LeaveQueuePrivateMetadataFactory $leaveQueuePrivateMetadataFactory,
        private LeaveQueueOptionsResolver $leaveQueueOptionsResolver,
    ) {
    }

    public function create(Queue $queue, UserTriggeredInteractionInterface $interaction): ?ModalSurface
    {
        $this->leaveQueuePrivateMetadataFactory->setQueue($queue)
            ->setResponseUrl($interaction->getResponseUrl());

        $this->leaveQueueOptionsResolver->setQueue($queue)->setUserId($interaction->getUserId());

        $this->modalInputsFactory
            ->setOptionsResolvers([$this->leaveQueueOptionsResolver])
            ->setDefaultValueResolvers([]);

        return $this->inputModalFactory
            ->setInputsFactory($this->modalInputsFactory)
            ->setPrivateMetadataFactory($this->leaveQueuePrivateMetadataFactory)
            ->create(
                $interaction,
                Modal::LEAVE_QUEUE,
                'Cancel',
                'Leave',
            );
    }
}

<?php

declare(strict_types=1);

namespace App\Slack\Surface\Factory\Modal;

use App\Entity\Workspace;
use App\Enum\Queue;
use App\Slack\Common\Component\UserTriggeredInteractionInterface;
use App\Slack\Surface\Component\Modal;
use App\Slack\Surface\Component\ModalSurface;
use App\Slack\Surface\Factory\InputModalFactory;
use App\Slack\Surface\Factory\Inputs\ModalInputsFactory;
use App\Slack\Surface\Factory\PrivateMetadata\AddQueuePrivateMetadataFactory;
use App\Slack\Surface\Factory\Resolver\AddQueue\AbstractAddQueueOptionsResolver;
use App\Slack\Surface\Factory\Resolver\AddQueue\AddQueueTypeDefaultValueResolver;
use App\Slack\Surface\Factory\Resolver\OptionsResolverInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

readonly class AddQueueModalFactory
{
    public function __construct(
        private InputModalFactory $inputModalFactory,
        private ModalInputsFactory $modalInputsFactory,
        private AddQueuePrivateMetadataFactory $addQueuePrivateMetadataFactory,
        /** @var OptionsResolverInterface[] $optionsResolvers */
        #[AutowireIterator(AbstractAddQueueOptionsResolver::TAG)]
        private iterable $optionsResolvers,
        private AddQueueTypeDefaultValueResolver $addQueueTypeDefaultValueResolver,
    ) {
    }

    public function create(
        ?Queue $queueType,
        UserTriggeredInteractionInterface $interaction,
        Workspace $workspace,
    ): ?ModalSurface {
        $modalType = match ($queueType) {
            null => Modal::ADD_QUEUE,
            Queue::SIMPLE => Modal::ADD_QUEUE_SIMPLE,
            Queue::DEPLOYMENT => Modal::ADD_QUEUE_DEPLOYMENT,
        };

        $this->setUpOptionsResolvers($workspace);

        $this->addQueueTypeDefaultValueResolver->setQueue($queueType);
        $this->addQueuePrivateMetadataFactory->setQueue($queueType)
            ->setResponseUrl($interaction->getResponseUrl());

        $this->modalInputsFactory
            ->setOptionsResolvers($this->optionsResolvers)
            ->setDefaultValueResolvers([$this->addQueueTypeDefaultValueResolver]);

        return $this->inputModalFactory
            ->setInputsFactory($this->modalInputsFactory)
            ->setPrivateMetadataFactory($this->addQueuePrivateMetadataFactory)
            ->create(
                $interaction,
                $modalType,
                'Cancel',
                'Save',
            );
    }

    private function setUpOptionsResolvers(Workspace $workspace): void
    {
        foreach ($this->optionsResolvers as $resolver) {
            if ($resolver instanceof AbstractAddQueueOptionsResolver) {
                $resolver->setWorkspace($workspace);
            }
        }
    }
}

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
use App\Slack\Surface\Factory\PrivateMetadata\EditQueuePrivateMetadataFactory;
use App\Slack\Surface\Factory\Resolver\AddQueue\AddQueueBehaviourOptionsResolver;
use App\Slack\Surface\Factory\Resolver\AddQueue\AddQueueRepositoryOptionsResolver;
use App\Slack\Surface\Factory\Resolver\DefaultValueResolverInterface;
use App\Slack\Surface\Factory\Resolver\Queue\AbstractQueueDefaultValueResolver;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

readonly class EditQueueModalFactory
{
    public function __construct(
        private InputModalFactory $inputModalFactory,
        private ModalInputsFactory $modalInputsFactory,
        private EditQueuePrivateMetadataFactory $editQueuePrivateMetadataFactory,
        /** @var DefaultValueResolverInterface[] $defaultValueResolvers */
        #[AutowireIterator(AbstractQueueDefaultValueResolver::TAG)]
        private iterable $defaultValueResolvers,
        private AddQueueRepositoryOptionsResolver $repositoryOptionsResolver,
        private AddQueueBehaviourOptionsResolver $behaviourOptionsResolver,
    ) {
    }

    public function create(Queue $queue, UserTriggeredInteractionInterface $interaction): ?ModalSurface
    {
        $this->setUpDefaultValueResolvers($queue);

        $this->repositoryOptionsResolver->setWorkspace($queue->getWorkspace());

        $this->modalInputsFactory->setDefaultValueResolvers($this->defaultValueResolvers)
            ->setOptionsResolvers([
                $this->repositoryOptionsResolver,
                $this->behaviourOptionsResolver,
            ]);

        $this->editQueuePrivateMetadataFactory->setQueue($queue)
            ->setResponseUrl($interaction->getResponseUrl());

        $modal = match (true) {
            $queue instanceof DeploymentQueue => Modal::EDIT_QUEUE_DEPLOYMENT,
            default => Modal::EDIT_QUEUE,
        };

        return $this->inputModalFactory
            ->setInputsFactory($this->modalInputsFactory)
            ->setPrivateMetadataFactory($this->editQueuePrivateMetadataFactory)
            ->create(
                $interaction,
                $modal,
                'Cancel',
                'Save',
            );
    }

    private function setUpDefaultValueResolvers(Queue $queue): void
    {
        foreach ($this->defaultValueResolvers as $resolver) {
            if ($resolver instanceof AbstractQueueDefaultValueResolver) {
                $resolver->setQueue($queue);
            }
        }
    }
}

<?php

declare(strict_types=1);

namespace App\Slack\Surface\Factory\Modal;

use App\Entity\Repository;
use App\Slack\Common\Component\UserTriggeredInteractionInterface;
use App\Slack\Surface\Component\Modal;
use App\Slack\Surface\Component\ModalSurface;
use App\Slack\Surface\Factory\InputModalFactory;
use App\Slack\Surface\Factory\Inputs\ModalInputsFactory;
use App\Slack\Surface\Factory\PrivateMetadata\EditRepositoryPrivateMetadataFactory;
use App\Slack\Surface\Factory\Resolver\DefaultValueResolverInterface;
use App\Slack\Surface\Factory\Resolver\OptionsResolverInterface;
use App\Slack\Surface\Factory\Resolver\Repository\AbstractRepositoryDefaultValueResolver;
use App\Slack\Surface\Factory\Resolver\Repository\AbstractRepositoryOptionsResolver;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

readonly class EditRepositoryModalFactory
{
    public function __construct(
        private InputModalFactory $inputModalFactory,
        private ModalInputsFactory $modalInputsFactory,
        private EditRepositoryPrivateMetadataFactory $editRepositoryPrivateMetadataFactory,
        /** @var DefaultValueResolverInterface[] $defaultValueResolvers */
        #[AutowireIterator(AbstractRepositoryDefaultValueResolver::TAG)]
        private iterable $defaultValueResolvers,
        /** @var OptionsResolverInterface[] $optionsResolvers */
        #[AutowireIterator(AbstractRepositoryOptionsResolver::TAG)]
        private iterable $optionsResolvers,
    ) {
    }

    public function create(Repository $repository, UserTriggeredInteractionInterface $interaction): ?ModalSurface
    {
        $this->setUpDefaultValueResolvers($repository)
            ->setUpOptionsResolvers($interaction->getTeamId());

        $this->modalInputsFactory
            ->setDefaultValueResolvers($this->defaultValueResolvers)
            ->setOptionsResolvers($this->optionsResolvers);

        $this->editRepositoryPrivateMetadataFactory->setRepository($repository)
            ->setResponseUrl($interaction->getResponseUrl());

        return $this->inputModalFactory
            ->setInputsFactory($this->modalInputsFactory)
            ->setPrivateMetadataFactory($this->editRepositoryPrivateMetadataFactory)
            ->create(
                $interaction,
                Modal::EDIT_REPOSITORY,
                'Cancel',
                'Save',
            );
    }

    private function setUpDefaultValueResolvers(Repository $repository): static
    {
        foreach ($this->defaultValueResolvers as $resolver) {
            if ($resolver instanceof AbstractRepositoryDefaultValueResolver) {
                $resolver->setRepository($repository);
            }
        }

        return $this;
    }

    private function setUpOptionsResolvers(string $teamId): static
    {
        foreach ($this->optionsResolvers as $resolver) {
            if ($resolver instanceof AbstractRepositoryOptionsResolver) {
                $resolver->setTeamId($teamId);
            }
        }

        return $this;
    }
}

<?php

declare(strict_types=1);

namespace App\Slack\Surface\Factory\Modal;

use App\Slack\Common\Component\UserTriggeredInteractionInterface;
use App\Slack\Surface\Component\Modal;
use App\Slack\Surface\Component\ModalSurface;
use App\Slack\Surface\Factory\InputModalFactory;
use App\Slack\Surface\Factory\Inputs\ModalInputsFactory;
use App\Slack\Surface\Factory\PrivateMetadata\AddRepositoryPrivateMetadataFactory;
use App\Slack\Surface\Factory\Resolver\OptionsResolverInterface;
use App\Slack\Surface\Factory\Resolver\Repository\AbstractRepositoryOptionsResolver;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

readonly class AddRepositoryModalFactory
{
    public function __construct(
        private InputModalFactory $inputModalFactory,
        private ModalInputsFactory $modalInputsFactory,
        private AddRepositoryPrivateMetadataFactory $addRepositoryPrivateMetadataFactory,
        /** @var OptionsResolverInterface[] $optionsResolvers */
        #[AutowireIterator(AbstractRepositoryOptionsResolver::TAG)]
        private iterable $optionsResolvers,
    ) {
    }

    public function create(UserTriggeredInteractionInterface $interaction): ?ModalSurface
    {
        $this->setUpOptionsResolvers($interaction->getTeamId());
        $this->addRepositoryPrivateMetadataFactory->setResponseUrl($interaction->getResponseUrl());

        $this->modalInputsFactory
            ->setOptionsResolvers($this->optionsResolvers)
            ->setDefaultValueResolvers([]);

        return $this->inputModalFactory
            ->setInputsFactory($this->modalInputsFactory)
            ->setPrivateMetadataFactory($this->addRepositoryPrivateMetadataFactory)
            ->create(
                $interaction,
                Modal::ADD_REPOSITORY,
                'Cancel',
                'Save',
            );
    }

    private function setUpOptionsResolvers(string $teamId): void
    {
        foreach ($this->optionsResolvers as $resolver) {
            if ($resolver instanceof AbstractRepositoryOptionsResolver) {
                $resolver->setTeamId($teamId);
            }
        }
    }
}

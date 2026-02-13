<?php

declare(strict_types=1);

namespace App\Slack\Surface\Factory\Modal;

use App\Entity\User;
use App\Slack\Common\Component\UserTriggeredInteractionInterface;
use App\Slack\Surface\Component\Modal;
use App\Slack\Surface\Component\ModalSurface;
use App\Slack\Surface\Factory\InputModalFactory;
use App\Slack\Surface\Factory\Inputs\ModalInputsFactory;
use App\Slack\Surface\Factory\PrivateMetadata\ConfigurationPrivateMetadataFactory;
use App\Slack\Surface\Factory\Resolver\Configuration\ConfigurationDefaultValueResolverInterface;
use App\Slack\Surface\Factory\Resolver\Configuration\ConfigurationOptionsResolverInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

readonly class ConfigurationModalFactory
{
    public function __construct(
        private InputModalFactory $inputModalFactory,
        private ModalInputsFactory $modalInputsFactory,
        private ConfigurationPrivateMetadataFactory $privateMetadataFactory,
        /** @var ConfigurationDefaultValueResolverInterface[] */
        #[AutowireIterator(ConfigurationDefaultValueResolverInterface::class)]
        private iterable $defaultValueResolvers,
        /** @var ConfigurationOptionsResolverInterface[] */
        #[AutowireIterator(ConfigurationOptionsResolverInterface::class)]
        private iterable $optionsResolvers,
    ) {
    }

    public function create(User $user, UserTriggeredInteractionInterface $interaction): ?ModalSurface
    {
        $this->setUpDefaultValueResolvers($user);

        $this->privateMetadataFactory->setResponseUrl($interaction->getResponseUrl());

        $this->modalInputsFactory
            ->setDefaultValueResolvers($this->defaultValueResolvers)
            ->setOptionsResolvers($this->optionsResolvers);

        return $this->inputModalFactory
            ->setInputsFactory($this->modalInputsFactory)
            ->setPrivateMetadataFactory($this->privateMetadataFactory)
            ->create($interaction, Modal::CONFIGURATION, 'Cancel', 'Save');
    }

    private function setUpDefaultValueResolvers(User $user): void
    {
        foreach ($this->defaultValueResolvers as $resolver) {
            $resolver->setUser($user);
        }
    }
}

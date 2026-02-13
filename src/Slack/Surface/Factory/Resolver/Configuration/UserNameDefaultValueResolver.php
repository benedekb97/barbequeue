<?php

declare(strict_types=1);

namespace App\Slack\Surface\Factory\Resolver\Configuration;

use App\Slack\Surface\Component\ModalArgument;

class UserNameDefaultValueResolver extends AbstractConfigurationDefaultValueResolver implements ConfigurationDefaultValueResolverInterface
{
    public function getSupportedArgument(): ModalArgument
    {
        return ModalArgument::CONFIGURATION_USER_NAME;
    }

    public function resolveString(): ?string
    {
        return $this->user?->getName();
    }

    public function resolveArray(): ?array
    {
        return null;
    }
}

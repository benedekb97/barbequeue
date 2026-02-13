<?php

declare(strict_types=1);

namespace App\Slack\Surface\Factory\Resolver\Configuration;

use App\Entity\User;
use App\Slack\Surface\Factory\Resolver\DefaultValueResolverInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag]
interface ConfigurationDefaultValueResolverInterface extends DefaultValueResolverInterface
{
    public function setUser(?User $user): void;
}

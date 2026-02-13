<?php

declare(strict_types=1);

namespace App\Slack\Surface\Factory\Resolver\Configuration;

use App\Entity\User;
use App\Slack\Surface\Factory\Resolver\DefaultValueResolverInterface;

abstract class AbstractConfigurationDefaultValueResolver implements DefaultValueResolverInterface
{
    protected ?User $user = null;

    public function setUser(?User $user): void
    {
        $this->user = $user;
    }
}

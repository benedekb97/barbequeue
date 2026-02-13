<?php

declare(strict_types=1);

namespace App\Slack\Surface\Factory\Resolver\Repository;

use App\Slack\Surface\Component\ModalArgument;
use App\Slack\Surface\Factory\Resolver\DefaultValueResolverInterface;

class NameDefaultValueResolver extends AbstractRepositoryDefaultValueResolver implements DefaultValueResolverInterface
{
    public function getSupportedArgument(): ModalArgument
    {
        return ModalArgument::REPOSITORY_NAME;
    }

    public function resolveString(): ?string
    {
        return $this->repository?->getName();
    }

    public function resolveArray(): ?array
    {
        return null;
    }
}

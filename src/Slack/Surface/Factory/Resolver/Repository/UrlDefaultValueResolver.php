<?php

declare(strict_types=1);

namespace App\Slack\Surface\Factory\Resolver\Repository;

use App\Slack\Surface\Component\ModalArgument;
use App\Slack\Surface\Factory\Resolver\DefaultValueResolverInterface;

class UrlDefaultValueResolver extends AbstractRepositoryDefaultValueResolver implements DefaultValueResolverInterface
{
    public function getSupportedArgument(): ModalArgument
    {
        return ModalArgument::REPOSITORY_URL;
    }

    public function resolveString(): ?string
    {
        return $this->repository?->getUrl();
    }

    public function resolveArray(): ?array
    {
        return null;
    }
}

<?php

declare(strict_types=1);

namespace App\Slack\Surface\Factory\Resolver\Repository;

use App\Entity\Repository;
use App\Slack\Surface\Factory\Resolver\DefaultValueResolverInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag(self::TAG)]
abstract class AbstractRepositoryDefaultValueResolver implements DefaultValueResolverInterface
{
    public const string TAG = 'app.slack.resolver.default_value.repository';

    protected ?Repository $repository = null;

    public function setRepository(?Repository $repository): static
    {
        $this->repository = $repository;

        return $this;
    }
}

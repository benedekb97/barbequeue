<?php

declare(strict_types=1);

namespace App\Slack\Surface\Factory\Resolver\Repository;

use App\Slack\Surface\Factory\Resolver\OptionsResolverInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag(self::TAG)]
abstract class AbstractRepositoryOptionsResolver implements OptionsResolverInterface
{
    public const string TAG = 'app.slack.resolver.options.repository';

    protected ?string $teamId = null;

    public function setTeamId(?string $teamId): static
    {
        $this->teamId = $teamId;

        return $this;
    }
}

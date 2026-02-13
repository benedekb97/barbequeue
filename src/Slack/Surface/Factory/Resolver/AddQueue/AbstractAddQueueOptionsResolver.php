<?php

declare(strict_types=1);

namespace App\Slack\Surface\Factory\Resolver\AddQueue;

use App\Entity\Workspace;
use App\Slack\Surface\Factory\Resolver\OptionsResolverInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag(self::TAG)]
abstract class AbstractAddQueueOptionsResolver implements OptionsResolverInterface
{
    public const string TAG = 'app.slack.resolver.options.add_queue';

    protected ?Workspace $workspace = null;

    public function setWorkspace(?Workspace $workspace): static
    {
        $this->workspace = $workspace;

        return $this;
    }
}

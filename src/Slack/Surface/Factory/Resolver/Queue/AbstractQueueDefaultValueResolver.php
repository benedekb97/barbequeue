<?php

declare(strict_types=1);

namespace App\Slack\Surface\Factory\Resolver\Queue;

use App\Entity\Queue;
use App\Slack\Surface\Factory\Resolver\DefaultValueResolverInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag(self::TAG)]
abstract class AbstractQueueDefaultValueResolver implements DefaultValueResolverInterface
{
    public const string TAG = 'app.slack.resolver.default_value.queue';

    protected ?Queue $queue = null;

    public function setQueue(?Queue $queue): static
    {
        $this->queue = $queue;

        return $this;
    }
}

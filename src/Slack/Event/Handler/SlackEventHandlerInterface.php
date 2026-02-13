<?php

declare(strict_types=1);

namespace App\Slack\Event\Handler;

use App\Slack\Event\Component\SlackEventInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag(self::TAG)]
interface SlackEventHandlerInterface
{
    public const string TAG = 'app.slack.event_handler';

    public function supports(SlackEventInterface $event): bool;

    public function handle(SlackEventInterface $event): void;
}

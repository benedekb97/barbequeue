<?php

declare(strict_types=1);

namespace App\Slack\Event\Factory;

use App\Slack\Event\Component\SlackEventInterface;
use App\Slack\Event\Event;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\HttpFoundation\Request;

#[AutoconfigureTag(self::TAG)]
interface SlackEventFactoryInterface
{
    public const string TAG = 'app.slack.event_factory';

    public function supports(Event $event): bool;

    public function create(Request $request): SlackEventInterface;
}

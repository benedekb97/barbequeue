<?php

declare(strict_types=1);

namespace App\Slack\Interaction\Handler;

use App\Slack\Interaction\SlackInteraction;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag(self::TAG)]
interface SlackInteractionHandlerInterface
{
    public const string TAG = 'app.slack_interaction_handler';

    public function supports(SlackInteraction $interaction): bool;

    public function handle(SlackInteraction $interaction): void;
}

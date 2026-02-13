<?php

declare(strict_types=1);

namespace App\Slack\Command\Handler;

use App\Slack\Command\SlackCommand;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag(self::TAG)]
interface SlackCommandHandlerInterface
{
    public const string TAG = 'app.slack_command_handler';

    public function supports(SlackCommand $command): bool;

    public function handle(SlackCommand $command): void;
}

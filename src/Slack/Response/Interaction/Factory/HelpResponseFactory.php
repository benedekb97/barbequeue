<?php

declare(strict_types=1);

namespace App\Slack\Response\Interaction\Factory;

use App\Slack\Block\Component\SectionBlock;
use App\Slack\Command\Command;
use App\Slack\Command\SubCommand;
use App\Slack\Response\Interaction\SlackInteractionResponse;

class HelpResponseFactory
{
    public function create(Command $command, ?SubCommand $subCommand): SlackInteractionResponse
    {
        if (null !== $subCommand) {
            return new SlackInteractionResponse(array_filter([
                $this->getHelpSection($command, $subCommand),
            ]));
        }

        return new SlackInteractionResponse(array_filter([
            ...array_map(function (?SubCommand $subCommand) use ($command) {
                return $this->getHelpSection($command, $subCommand, true);
            }, $command->getSubCommands()),
        ]));
    }

    private function getHelpSection(Command $command, ?SubCommand $subCommand, bool $multiple = false): ?SectionBlock
    {
        if (null === $subCommand) {
            return null;
        }

        return new SectionBlock(sprintf(
            '%s%s %s   %s',
            $multiple ? '• ' : '',
            $command->getHelpText($subCommand),
            PHP_EOL,
            $command->getUsage($subCommand),
        ));
    }
}

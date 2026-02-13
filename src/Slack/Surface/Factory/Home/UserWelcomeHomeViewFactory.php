<?php

declare(strict_types=1);

namespace App\Slack\Surface\Factory\Home;

use App\Entity\Workspace;
use App\Slack\Block\Component\DividerBlock;
use App\Slack\Block\Component\HeaderBlock;
use App\Slack\Block\Component\SectionBlock;
use App\Slack\Command\Command;
use App\Slack\Command\SubCommand;
use App\Slack\Surface\Component\HomeSurface;

readonly class UserWelcomeHomeViewFactory
{
    public function create(string $userId, Workspace $workspace): HomeSurface
    {
        return new HomeSurface(
            $userId,
            $workspace,
            [
                new HeaderBlock('Welcome to BarbeQueue!'),
                new SectionBlock('Read about how to use BarbeQueue below.'),
                new DividerBlock(),
                new SectionBlock('BBQ allows users to join a queue more than once. There may also be a limit on how long you can hold the front of the queue for. It all depends on the settings for the specific queue in question.'),
                new HeaderBlock('Commands'),
                $this->getUsageBlock(Command::BBQ, SubCommand::JOIN),
                $this->getUsageBlock(Command::BBQ, SubCommand::LEAVE),
                $this->getUsageBlock(Command::BBQ, SubCommand::LIST),
            ],
        );
    }

    private function getUsageBlock(Command $command, SubCommand $subCommand): SectionBlock
    {
        return new SectionBlock(sprintf(
            '• %s %s   %s',
            $command->getHelpText($subCommand),
            PHP_EOL,
            $command->getUsage($subCommand),
        ));
    }
}

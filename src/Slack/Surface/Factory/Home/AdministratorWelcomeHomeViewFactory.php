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

readonly class AdministratorWelcomeHomeViewFactory
{
    public function create(string $userId, Workspace $workspace): HomeSurface
    {
        return new HomeSurface($userId, $workspace, [
            new HeaderBlock('Welcome to BarbeQueue!'),
            new SectionBlock('Read more about how to administer your application below.'),
            new DividerBlock(),
            new HeaderBlock('Admin commands'),
            new SectionBlock('*Repositories* - Link them to your queues to track releases or define blockers _(upcoming feature)_'),
            $this->getUsageBlock(Command::BBQ_ADMIN, SubCommand::LIST_REPOSITORIES),
            $this->getUsageBlock(Command::BBQ_ADMIN, SubCommand::ADD_REPOSITORY),
            $this->getUsageBlock(Command::BBQ_ADMIN, SubCommand::EDIT_REPOSITORY),
            $this->getUsageBlock(Command::BBQ_ADMIN, SubCommand::REMOVE_REPOSITORY),
            new SectionBlock('*Queues* - Take control of your queues'),
            $this->getUsageBlock(Command::BBQ_ADMIN, SubCommand::EDIT_QUEUE),
            $this->getUsageBlock(Command::BBQ_ADMIN, SubCommand::POP_QUEUE),
            new SectionBlock('*Administrators* - Give or revoke access to BBQ admin commands'),
            $this->getUsageBlock(Command::BBQ_ADMIN, SubCommand::ADD_USER),
            $this->getUsageBlock(Command::BBQ_ADMIN, SubCommand::REMOVE_USER),
            new DividerBlock(),
            new HeaderBlock('User commands'),
            $this->getUsageBlock(Command::BBQ, SubCommand::JOIN),
            $this->getUsageBlock(Command::BBQ, SubCommand::LEAVE),
            $this->getUsageBlock(Command::BBQ, SubCommand::LIST),
        ]);
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

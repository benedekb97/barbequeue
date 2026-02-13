<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Command\Handler;

use App\Slack\Command\Command;
use App\Slack\Command\Handler\SlackCommandHandlerInterface;
use App\Slack\Command\SlackCommand;
use App\Slack\Command\SubCommand;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

abstract class AbstractCommandHandlerTestCase extends KernelTestCase
{
    abstract protected function getSupportedCommand(): Command;

    abstract protected function getSupportedSubCommand(): SubCommand;

    abstract protected function getUnsupportedCommand(): Command;

    abstract protected function getUnsupportedSubCommand(): SubCommand;

    abstract protected function getHandler(): SlackCommandHandlerInterface;

    #[Test]
    public function itShouldSupportSupportedCommand(): void
    {
        $command = $this->createMock(SlackCommand::class);
        $command->expects($this->once())
            ->method('getCommand')
            ->willReturn($this->getSupportedCommand());

        $command->expects($this->once())
            ->method('getSubCommand')
            ->willReturn($this->getSupportedSubCommand());

        $this->assertTrue($this->getHandler()->supports($command));
    }

    #[Test]
    public function itShouldNotSupportUnsupportedSubCommand(): void
    {
        $command = $this->createMock(SlackCommand::class);
        $command->expects($this->once())
            ->method('getCommand')
            ->willReturn($this->getSupportedCommand());

        $command->expects($this->once())
            ->method('getSubCommand')
            ->willReturn($this->getUnsupportedSubCommand());

        $this->assertFalse($this->getHandler()->supports($command));
    }

    #[Test]
    public function itShouldNotSupportUnsupportedCommand(): void
    {
        $command = $this->createMock(SlackCommand::class);
        $command->expects($this->once())
            ->method('getCommand')
            ->willReturn($this->getUnsupportedCommand());

        $command->expects($this->never())
            ->method('getSubCommand');

        $this->assertFalse($this->getHandler()->supports($command));
    }
}

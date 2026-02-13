<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Command\Handler;

use App\Slack\Command\Command;
use App\Slack\Command\CommandArgument;
use App\Slack\Command\Exception\InvalidSubCommandException;
use App\Slack\Command\Handler\AdministratorHelpCommandHandler;
use App\Slack\Command\Handler\SlackCommandHandlerInterface;
use App\Slack\Command\Resolver\SubCommandResolver;
use App\Slack\Command\SlackCommand;
use App\Slack\Command\SubCommand;
use App\Slack\Common\Validator\AuthorisationValidator;
use App\Slack\Response\Interaction\Factory\Administrator\UnauthorisedResponseFactory;
use App\Slack\Response\Interaction\Factory\HelpResponseFactory;
use App\Slack\Response\Interaction\SlackInteractionResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(AdministratorHelpCommandHandler::class)]
class AdministratorHelpCommandHandlerTest extends AbstractCommandHandlerTestCase
{
    #[Test]
    public function itShouldCreateHelpResponse(): void
    {
        $slackCommand = $this->createMock(SlackCommand::class);
        $slackCommand->expects($this->exactly(2))
            ->method('getCommand')
            ->willReturn($command = Command::BBQ_ADMIN);

        $slackCommand->expects($this->once())
            ->method('getOptionalArgumentString')
            ->with(CommandArgument::COMMAND)
            ->willReturn(null);

        $resolver = $this->createMock(SubCommandResolver::class);
        $resolver->expects($this->once())
            ->method('resolveFromString')
            ->with($command, null)
            ->willThrowException($this->createStub(InvalidSubCommandException::class));

        $responseFactory = $this->createMock(HelpResponseFactory::class);
        $responseFactory->expects($this->once())
            ->method('create')
            ->with($command, null)
            ->willReturn($response = $this->createStub(SlackInteractionResponse::class));

        $slackCommand->expects($this->once())
            ->method('setResponse')
            ->with($response);

        $handler = new AdministratorHelpCommandHandler(
            $this->createStub(AuthorisationValidator::class),
            $this->createStub(UnauthorisedResponseFactory::class),
            $resolver,
            $responseFactory,
        );

        $handler->run($slackCommand);
    }

    #[Test]
    public function itShouldCreateHelpResponseIfSubCommandSupplied(): void
    {
        $slackCommand = $this->createMock(SlackCommand::class);
        $slackCommand->expects($this->exactly(2))
            ->method('getCommand')
            ->willReturn($command = Command::BBQ_ADMIN);

        $slackCommand->expects($this->once())
            ->method('getOptionalArgumentString')
            ->with(CommandArgument::COMMAND)
            ->willReturn($commandArgument = 'join');

        $resolver = $this->createMock(SubCommandResolver::class);
        $resolver->expects($this->once())
            ->method('resolveFromString')
            ->with($command, $commandArgument)
            ->willReturn($subCommand = SubCommand::JOIN);

        $responseFactory = $this->createMock(HelpResponseFactory::class);
        $responseFactory->expects($this->once())
            ->method('create')
            ->with($command, $subCommand)
            ->willReturn($response = $this->createStub(SlackInteractionResponse::class));

        $slackCommand->expects($this->once())
            ->method('setResponse')
            ->with($response);

        $handler = new AdministratorHelpCommandHandler(
            $this->createStub(AuthorisationValidator::class),
            $this->createStub(UnauthorisedResponseFactory::class),
            $resolver,
            $responseFactory,
        );

        $handler->run($slackCommand);
    }

    protected function getSupportedCommand(): Command
    {
        return Command::BBQ_ADMIN;
    }

    protected function getSupportedSubCommand(): SubCommand
    {
        return SubCommand::HELP;
    }

    protected function getUnsupportedCommand(): Command
    {
        return Command::BBQ;
    }

    protected function getUnsupportedSubCommand(): SubCommand
    {
        return SubCommand::JOIN;
    }

    protected function getHandler(): SlackCommandHandlerInterface
    {
        return new AdministratorHelpCommandHandler(
            $this->createStub(AuthorisationValidator::class),
            $this->createStub(UnauthorisedResponseFactory::class),
            $this->createStub(SubCommandResolver::class),
            $this->createStub(HelpResponseFactory::class),
        );
    }
}

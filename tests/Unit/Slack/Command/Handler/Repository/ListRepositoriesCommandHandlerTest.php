<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Command\Handler\Repository;

use App\Entity\Administrator;
use App\Entity\Workspace;
use App\Slack\Command\Command;
use App\Slack\Command\Handler\Repository\ListRepositoriesCommandHandler;
use App\Slack\Command\Handler\SlackCommandHandlerInterface;
use App\Slack\Command\SlackCommand;
use App\Slack\Command\SubCommand;
use App\Slack\Common\Validator\AuthorisationValidator;
use App\Slack\Response\Interaction\Factory\Administrator\UnauthorisedResponseFactory;
use App\Slack\Response\Interaction\Factory\Repository\ListRepositoriesResponseFactory;
use App\Slack\Response\Interaction\SlackInteractionResponse;
use App\Tests\Unit\Slack\Command\Handler\AbstractCommandHandlerTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(ListRepositoriesCommandHandler::class)]
class ListRepositoriesCommandHandlerTest extends AbstractCommandHandlerTestCase
{
    #[Test]
    public function itShouldCreateUnauthorisedResponseIfWorkspaceIsNull(): void
    {
        $command = $this->createMock(SlackCommand::class);
        $command->expects($this->once())
            ->method('getAdministrator')
            ->willReturn(null);

        $responseFactory = $this->createMock(UnauthorisedResponseFactory::class);
        $responseFactory->expects($this->once())
            ->method('create')
            ->willReturn($response = $this->createStub(SlackInteractionResponse::class));

        $command->expects($this->once())
            ->method('setResponse')
            ->with($response);

        $handler = new ListRepositoriesCommandHandler(
            $this->createStub(AuthorisationValidator::class),
            $responseFactory,
            $this->createStub(ListRepositoriesResponseFactory::class),
        );

        $handler->run($command);
    }

    #[Test]
    public function itShouldCreateListRepositoriesResponse(): void
    {
        $administrator = $this->createMock(Administrator::class);
        $administrator->expects($this->once())
            ->method('getWorkspace')
            ->willReturn($workspace = $this->createStub(Workspace::class));

        $command = $this->createMock(SlackCommand::class);
        $command->expects($this->once())
            ->method('getAdministrator')
            ->willReturn($administrator);

        $responseFactory = $this->createMock(ListRepositoriesResponseFactory::class);
        $responseFactory->expects($this->once())
            ->method('create')
            ->with($workspace)
            ->willReturn($response = $this->createStub(SlackInteractionResponse::class));

        $command->expects($this->once())
            ->method('setResponse')
            ->with($response);

        $handler = new ListRepositoriesCommandHandler(
            $this->createStub(AuthorisationValidator::class),
            $this->createStub(UnauthorisedResponseFactory::class),
            $responseFactory,
        );

        $handler->run($command);
    }

    protected function getSupportedCommand(): Command
    {
        return Command::BBQ_ADMIN;
    }

    protected function getSupportedSubCommand(): SubCommand
    {
        return SubCommand::LIST_REPOSITORIES;
    }

    protected function getUnsupportedCommand(): Command
    {
        return Command::BBQ;
    }

    protected function getUnsupportedSubCommand(): SubCommand
    {
        return SubCommand::ADD_REPOSITORY;
    }

    protected function getHandler(): SlackCommandHandlerInterface
    {
        return new ListRepositoriesCommandHandler(
            $this->createStub(AuthorisationValidator::class),
            $this->createStub(UnauthorisedResponseFactory::class),
            $this->createStub(ListRepositoriesResponseFactory::class),
        );
    }
}

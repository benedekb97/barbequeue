<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Command\Handler\Repository;

use App\Entity\Repository;
use App\Repository\RepositoryRepositoryInterface;
use App\Slack\Command\Command;
use App\Slack\Command\CommandArgument;
use App\Slack\Command\Handler\Repository\RemoveRepositoryCommandHandler;
use App\Slack\Command\Handler\SlackCommandHandlerInterface;
use App\Slack\Command\SlackCommand;
use App\Slack\Command\SubCommand;
use App\Slack\Common\Validator\AuthorisationValidator;
use App\Slack\Response\Interaction\Factory\Administrator\UnauthorisedResponseFactory;
use App\Slack\Response\Interaction\Factory\Repository\ConfirmRemoveRepositoryResponseFactory;
use App\Slack\Response\Interaction\Factory\Repository\UnrecognisedRepositoryResponseFactory;
use App\Slack\Response\Interaction\SlackInteractionResponse;
use App\Tests\Unit\Slack\Command\Handler\AbstractCommandHandlerTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(RemoveRepositoryCommandHandler::class)]
class RemoveRepositoryCommandHandlerTest extends AbstractCommandHandlerTestCase
{
    #[Test]
    public function itShouldCreateUnrecognisedRepositoryResponseIfRepositoryCannotBeFound(): void
    {
        $repository = $this->createMock(RepositoryRepositoryInterface::class);
        $repository->expects($this->once())
            ->method('findOneByNameAndTeamId')
            ->with($name = 'name', $teamId = 'teamId')
            ->willReturn(null);

        $command = $this->createMock(SlackCommand::class);
        $command->expects($this->once())
            ->method('getArgumentString')
            ->with(CommandArgument::REPOSITORY)
            ->willReturn($name);

        $command->expects($this->once())
            ->method('getTeamId')
            ->willReturn($teamId);

        $responseFactory = $this->createMock(UnrecognisedRepositoryResponseFactory::class);
        $responseFactory->expects($this->once())
            ->method('create')
            ->with($name, $teamId)
            ->willReturn($response = $this->createStub(SlackInteractionResponse::class));

        $command->expects($this->once())
            ->method('setResponse')
            ->with($response);

        $handler = new RemoveRepositoryCommandHandler(
            $this->createStub(AuthorisationValidator::class),
            $this->createStub(UnauthorisedResponseFactory::class),
            $repository,
            $responseFactory,
            $this->createStub(ConfirmRemoveRepositoryResponseFactory::class),
        );

        $handler->run($command);
    }

    #[Test]
    public function itShouldCreateConfirmRemoveRepositoryResponse(): void
    {
        $repositoryRepository = $this->createMock(RepositoryRepositoryInterface::class);
        $repositoryRepository->expects($this->once())
            ->method('findOneByNameAndTeamId')
            ->with($name = 'name', $teamId = 'teamId')
            ->willReturn($repository = $this->createStub(Repository::class));

        $command = $this->createMock(SlackCommand::class);
        $command->expects($this->once())
            ->method('getArgumentString')
            ->with(CommandArgument::REPOSITORY)
            ->willReturn($name);

        $command->expects($this->once())
            ->method('getTeamId')
            ->willReturn($teamId);

        $responseFactory = $this->createMock(ConfirmRemoveRepositoryResponseFactory::class);
        $responseFactory->expects($this->once())
            ->method('create')
            ->with($repository)
            ->willReturn($response = $this->createStub(SlackInteractionResponse::class));

        $command->expects($this->once())
            ->method('setResponse')
            ->with($response);

        $handler = new RemoveRepositoryCommandHandler(
            $this->createStub(AuthorisationValidator::class),
            $this->createStub(UnauthorisedResponseFactory::class),
            $repositoryRepository,
            $this->createStub(UnrecognisedRepositoryResponseFactory::class),
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
        return SubCommand::REMOVE_REPOSITORY;
    }

    protected function getUnsupportedCommand(): Command
    {
        return Command::BBQ;
    }

    protected function getUnsupportedSubCommand(): SubCommand
    {
        return SubCommand::LIST_REPOSITORIES;
    }

    protected function getHandler(): SlackCommandHandlerInterface
    {
        return new RemoveRepositoryCommandHandler(
            $this->createStub(AuthorisationValidator::class),
            $this->createStub(UnauthorisedResponseFactory::class),
            $this->createStub(RepositoryRepositoryInterface::class),
            $this->createStub(UnrecognisedRepositoryResponseFactory::class),
            $this->createStub(ConfirmRemoveRepositoryResponseFactory::class),
        );
    }
}

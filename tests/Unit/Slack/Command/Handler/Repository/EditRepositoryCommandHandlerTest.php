<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Command\Handler\Repository;

use App\Entity\Administrator;
use App\Entity\Repository;
use App\Entity\Workspace;
use App\Repository\RepositoryRepositoryInterface;
use App\Slack\Command\Command;
use App\Slack\Command\CommandArgument;
use App\Slack\Command\Handler\Repository\EditRepositoryCommandHandler;
use App\Slack\Command\Handler\SlackCommandHandlerInterface;
use App\Slack\Command\SlackCommand;
use App\Slack\Command\SubCommand;
use App\Slack\Common\Validator\AuthorisationValidator;
use App\Slack\Response\Interaction\Factory\Administrator\UnauthorisedResponseFactory;
use App\Slack\Response\Interaction\Factory\GenericFailureResponseFactory;
use App\Slack\Response\Interaction\Factory\Repository\UnrecognisedRepositoryResponseFactory;
use App\Slack\Response\Interaction\SlackInteractionResponse;
use App\Slack\Surface\Component\ModalSurface;
use App\Slack\Surface\Factory\Modal\EditRepositoryModalFactory;
use App\Slack\Surface\Service\ModalService;
use App\Tests\Unit\Slack\Command\Handler\AbstractCommandHandlerTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(EditRepositoryCommandHandler::class)]
class EditRepositoryCommandHandlerTest extends AbstractCommandHandlerTestCase
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

        $factory = $this->createMock(UnrecognisedRepositoryResponseFactory::class);
        $factory->expects($this->once())
            ->method('create')
            ->with($name, $teamId)
            ->willReturn($response = $this->createStub(SlackInteractionResponse::class));

        $command->expects($this->once())
            ->method('setResponse')
            ->with($response);

        $handler = new EditRepositoryCommandHandler(
            $this->createStub(AuthorisationValidator::class),
            $this->createStub(UnauthorisedResponseFactory::class),
            $repository,
            $this->createStub(EditRepositoryModalFactory::class),
            $this->createStub(ModalService::class),
            $factory,
            $this->createStub(GenericFailureResponseFactory::class),
        );

        $handler->run($command);
    }

    #[Test]
    public function itShouldCreateGenericFailureResponseIfModalFactoryReturnsNull(): void
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

        $factory = $this->createMock(UnrecognisedRepositoryResponseFactory::class);
        $factory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $modalFactory = $this->createMock(EditRepositoryModalFactory::class);
        $modalFactory->expects($this->once())
            ->method('create')
            ->with($repository, $command)
            ->willReturn(null);

        $failureResponseFactory = $this->createMock(GenericFailureResponseFactory::class);
        $failureResponseFactory->expects($this->once())
            ->method('create')
            ->willReturn($response = $this->createStub(SlackInteractionResponse::class));

        $command->expects($this->once())
            ->method('setResponse')
            ->with($response);

        $handler = new EditRepositoryCommandHandler(
            $this->createStub(AuthorisationValidator::class),
            $this->createStub(UnauthorisedResponseFactory::class),
            $repositoryRepository,
            $modalFactory,
            $this->createStub(ModalService::class),
            $factory,
            $failureResponseFactory,
        );

        $handler->run($command);
    }

    #[Test]
    public function itShouldCreateModal(): void
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

        $factory = $this->createMock(UnrecognisedRepositoryResponseFactory::class);
        $factory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $modalFactory = $this->createMock(EditRepositoryModalFactory::class);
        $modalFactory->expects($this->once())
            ->method('create')
            ->with($repository, $command)
            ->willReturn($modal = $this->createStub(ModalSurface::class));

        $failureResponseFactory = $this->createMock(GenericFailureResponseFactory::class);
        $failureResponseFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $administrator = $this->createMock(Administrator::class);
        $administrator->expects($this->once())
            ->method('getWorkspace')
            ->willReturn($workspace = $this->createStub(Workspace::class));

        $command->expects($this->once())
            ->method('getAdministrator')
            ->willReturn($administrator);

        $modalService = $this->createMock(ModalService::class);
        $modalService->expects($this->once())
            ->method('createModal')
            ->with($modal, $workspace);

        $handler = new EditRepositoryCommandHandler(
            $this->createStub(AuthorisationValidator::class),
            $this->createStub(UnauthorisedResponseFactory::class),
            $repositoryRepository,
            $modalFactory,
            $modalService,
            $factory,
            $failureResponseFactory,
        );

        $handler->run($command);
    }

    protected function getSupportedCommand(): Command
    {
        return Command::BBQ_ADMIN;
    }

    protected function getSupportedSubCommand(): SubCommand
    {
        return SubCommand::EDIT_REPOSITORY;
    }

    protected function getUnsupportedCommand(): Command
    {
        return Command::BBQ;
    }

    protected function getUnsupportedSubCommand(): SubCommand
    {
        return SubCommand::REMOVE_REPOSITORY;
    }

    protected function getHandler(): SlackCommandHandlerInterface
    {
        return new EditRepositoryCommandHandler(
            $this->createStub(AuthorisationValidator::class),
            $this->createStub(UnauthorisedResponseFactory::class),
            $this->createStub(RepositoryRepositoryInterface::class),
            $this->createStub(EditRepositoryModalFactory::class),
            $this->createStub(ModalService::class),
            $this->createStub(UnrecognisedRepositoryResponseFactory::class),
            $this->createStub(GenericFailureResponseFactory::class),
        );
    }
}

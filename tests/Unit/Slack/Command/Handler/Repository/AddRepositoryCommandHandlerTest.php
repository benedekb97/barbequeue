<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Command\Handler\Repository;

use App\Entity\Administrator;
use App\Entity\Workspace;
use App\Slack\Command\Command;
use App\Slack\Command\Handler\Repository\AddRepositoryCommandHandler;
use App\Slack\Command\Handler\SlackCommandHandlerInterface;
use App\Slack\Command\SlackCommand;
use App\Slack\Command\SubCommand;
use App\Slack\Common\Validator\AuthorisationValidator;
use App\Slack\Response\Interaction\Factory\Administrator\UnauthorisedResponseFactory;
use App\Slack\Response\Interaction\SlackInteractionResponse;
use App\Slack\Surface\Component\ModalSurface;
use App\Slack\Surface\Factory\Modal\AddRepositoryModalFactory;
use App\Slack\Surface\Service\ModalService;
use App\Tests\Unit\Slack\Command\Handler\AbstractCommandHandlerTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(AddRepositoryCommandHandler::class)]
class AddRepositoryCommandHandlerTest extends AbstractCommandHandlerTestCase
{
    #[Test]
    public function itShouldCreateUnauthorisedResponseIfWorkspaceIsNull(): void
    {
        $administrator = $this->createMock(Administrator::class);
        $administrator->expects($this->once())
            ->method('getWorkspace')
            ->willReturn(null);

        $command = $this->createMock(SlackCommand::class);
        $command->expects($this->once())
            ->method('getCommand')
            ->willReturn($commandType = Command::BBQ_ADMIN);

        $command->expects($this->once())
            ->method('getUserId')
            ->willReturn($userId = 'userId');

        $command->expects($this->once())
            ->method('getTeamId')
            ->willReturn($teamId = 'teamId');

        $command->expects($this->once())
            ->method('setAdministrator')
            ->with($administrator);

        $command->expects($this->once())
            ->method('getAdministrator')
            ->willReturn($administrator);

        $validator = $this->createMock(AuthorisationValidator::class);
        $validator->expects($this->once())
            ->method('validate')
            ->with($commandType, $userId, $teamId)
            ->willReturn($administrator);

        $responseFactory = $this->createMock(UnauthorisedResponseFactory::class);
        $responseFactory->expects($this->once())
            ->method('create')
            ->with()
            ->willReturn($response = $this->createStub(SlackInteractionResponse::class));

        $command->expects($this->once())
            ->method('setResponse')
            ->with($response);

        $modalFactory = $this->createMock(AddRepositoryModalFactory::class);
        $modalFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $service = $this->createMock(ModalService::class);
        $service->expects($this->never())
            ->method('createModal')
            ->withAnyParameters();

        $handler = new AddRepositoryCommandHandler(
            $validator,
            $responseFactory,
            $modalFactory,
            $service
        );

        $handler->handle($command);
    }

    #[Test]
    public function itShouldReturnEarlyIfModalFactoryReturnsNull(): void
    {
        $administrator = $this->createMock(Administrator::class);
        $administrator->expects($this->once())
            ->method('getWorkspace')
            ->willReturn($workspace = $this->createStub(Workspace::class));

        $command = $this->createMock(SlackCommand::class);
        $command->expects($this->once())
            ->method('getCommand')
            ->willReturn($commandType = Command::BBQ_ADMIN);

        $command->expects($this->once())
            ->method('getUserId')
            ->willReturn($userId = 'userId');

        $command->expects($this->once())
            ->method('getTeamId')
            ->willReturn($teamId = 'teamId');

        $command->expects($this->once())
            ->method('setAdministrator')
            ->with($administrator);

        $command->expects($this->once())
            ->method('getAdministrator')
            ->willReturn($administrator);

        $validator = $this->createMock(AuthorisationValidator::class);
        $validator->expects($this->once())
            ->method('validate')
            ->with($commandType, $userId, $teamId)
            ->willReturn($administrator);

        $responseFactory = $this->createMock(UnauthorisedResponseFactory::class);
        $responseFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $command->expects($this->never())
            ->method('setResponse')
            ->withAnyParameters();

        $modalFactory = $this->createMock(AddRepositoryModalFactory::class);
        $modalFactory->expects($this->once())
            ->method('create')
            ->with($command)
            ->willReturn(null);

        $service = $this->createMock(ModalService::class);
        $service->expects($this->never())
            ->method('createModal')
            ->withAnyParameters();

        $handler = new AddRepositoryCommandHandler(
            $validator,
            $responseFactory,
            $modalFactory,
            $service
        );

        $handler->handle($command);
    }

    #[Test]
    public function itShouldCreateModal(): void
    {
        $administrator = $this->createMock(Administrator::class);
        $administrator->expects($this->once())
            ->method('getWorkspace')
            ->willReturn($workspace = $this->createStub(Workspace::class));

        $command = $this->createMock(SlackCommand::class);
        $command->expects($this->once())
            ->method('getCommand')
            ->willReturn($commandType = Command::BBQ_ADMIN);

        $command->expects($this->once())
            ->method('getUserId')
            ->willReturn($userId = 'userId');

        $command->expects($this->once())
            ->method('getTeamId')
            ->willReturn($teamId = 'teamId');

        $command->expects($this->once())
            ->method('setAdministrator')
            ->with($administrator);

        $command->expects($this->once())
            ->method('getAdministrator')
            ->willReturn($administrator);

        $validator = $this->createMock(AuthorisationValidator::class);
        $validator->expects($this->once())
            ->method('validate')
            ->with($commandType, $userId, $teamId)
            ->willReturn($administrator);

        $responseFactory = $this->createMock(UnauthorisedResponseFactory::class);
        $responseFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $command->expects($this->never())
            ->method('setResponse')
            ->withAnyParameters();

        $modalFactory = $this->createMock(AddRepositoryModalFactory::class);
        $modalFactory->expects($this->once())
            ->method('create')
            ->with($command)
            ->willReturn($modal = $this->createStub(ModalSurface::class));

        $service = $this->createMock(ModalService::class);
        $service->expects($this->once())
            ->method('createModal')
            ->with($modal, $workspace);

        $handler = new AddRepositoryCommandHandler(
            $validator,
            $responseFactory,
            $modalFactory,
            $service
        );

        $handler->handle($command);
    }

    protected function getSupportedCommand(): Command
    {
        return Command::BBQ_ADMIN;
    }

    protected function getSupportedSubCommand(): SubCommand
    {
        return SubCommand::ADD_REPOSITORY;
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
        return new AddRepositoryCommandHandler(
            $this->createStub(AuthorisationValidator::class),
            $this->createStub(UnauthorisedResponseFactory::class),
            $this->createStub(AddRepositoryModalFactory::class),
            $this->createStub(ModalService::class),
        );
    }
}

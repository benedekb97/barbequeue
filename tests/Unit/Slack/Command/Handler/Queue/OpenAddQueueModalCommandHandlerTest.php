<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Command\Handler\Queue;

use App\Entity\Administrator;
use App\Entity\Workspace;
use App\Slack\Command\Command;
use App\Slack\Command\Handler\Queue\OpenAddQueueModalCommandHandler;
use App\Slack\Command\Handler\SlackCommandHandlerInterface;
use App\Slack\Command\SlackCommand;
use App\Slack\Command\SubCommand;
use App\Slack\Common\Validator\AuthorisationValidator;
use App\Slack\Response\Interaction\Factory\Administrator\UnauthorisedResponseFactory;
use App\Slack\Surface\Component\ModalSurface;
use App\Slack\Surface\Factory\Modal\AddQueueModalFactory;
use App\Slack\Surface\Service\ModalService;
use App\Tests\Unit\Slack\Command\Handler\AbstractCommandHandlerTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(OpenAddQueueModalCommandHandler::class)]
class OpenAddQueueModalCommandHandlerTest extends AbstractCommandHandlerTestCase
{
    #[Test]
    public function itShouldReturnEarlyIfWorkspaceIsNull(): void
    {
        $command = $this->createMock(SlackCommand::class);
        $command->expects($this->once())
            ->method('getAdministrator')
            ->willReturn($administrator = $this->createMock(Administrator::class));

        $administrator->expects($this->once())
            ->method('getWorkspace')
            ->willReturn(null);

        $handler = new OpenAddQueueModalCommandHandler(
            $this->createStub(AuthorisationValidator::class),
            $this->createStub(UnauthorisedResponseFactory::class),
            $this->createStub(AddQueueModalFactory::class),
            $this->createStub(ModalService::class),
        );

        $handler->run($command);
    }

    #[Test]
    public function itShouldReturnEarlyIfModalCreationFailed(): void
    {
        $command = $this->createMock(SlackCommand::class);
        $command->expects($this->once())
            ->method('getAdministrator')
            ->willReturn($administrator = $this->createMock(Administrator::class));

        $administrator->expects($this->once())
            ->method('getWorkspace')
            ->willReturn($workspace = $this->createStub(Workspace::class));

        $modalFactory = $this->createMock(AddQueueModalFactory::class);
        $modalFactory->expects($this->once())
            ->method('create')
            ->with(null, $command, $workspace)
            ->willReturn(null);

        $handler = new OpenAddQueueModalCommandHandler(
            $this->createStub(AuthorisationValidator::class),
            $this->createStub(UnauthorisedResponseFactory::class),
            $modalFactory,
            $this->createStub(ModalService::class),
        );

        $handler->run($command);
    }

    #[Test]
    public function itShouldCreateAddQueueModal(): void
    {
        $command = $this->createMock(SlackCommand::class);
        $command->expects($this->once())
            ->method('getAdministrator')
            ->willReturn($administrator = $this->createMock(Administrator::class));

        $administrator->expects($this->once())
            ->method('getWorkspace')
            ->willReturn($workspace = $this->createStub(Workspace::class));

        $modalFactory = $this->createMock(AddQueueModalFactory::class);
        $modalFactory->expects($this->once())
            ->method('create')
            ->with(null, $command, $workspace)
            ->willReturn($modal = $this->createStub(ModalSurface::class));

        $modalService = $this->createMock(ModalService::class);
        $modalService->expects($this->once())
            ->method('createModal')
            ->with($modal, $workspace);

        $handler = new OpenAddQueueModalCommandHandler(
            $this->createStub(AuthorisationValidator::class),
            $this->createStub(UnauthorisedResponseFactory::class),
            $modalFactory,
            $modalService,
        );

        $handler->run($command);
    }

    protected function getSupportedCommand(): Command
    {
        return Command::BBQ_ADMIN;
    }

    protected function getSupportedSubCommand(): SubCommand
    {
        return SubCommand::ADD_QUEUE;
    }

    protected function getUnsupportedCommand(): Command
    {
        return Command::BBQ;
    }

    protected function getUnsupportedSubCommand(): SubCommand
    {
        return SubCommand::EDIT_QUEUE;
    }

    protected function getHandler(): SlackCommandHandlerInterface
    {
        return new OpenAddQueueModalCommandHandler(
            $this->createStub(AuthorisationValidator::class),
            $this->createStub(UnauthorisedResponseFactory::class),
            $this->createStub(AddQueueModalFactory::class),
            $this->createStub(ModalService::class),
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Command\Handler;

use App\Entity\User;
use App\Entity\Workspace;
use App\Repository\WorkspaceRepositoryInterface;
use App\Resolver\UserResolver;
use App\Slack\Command\Command;
use App\Slack\Command\Handler\ConfigureCommandHandler;
use App\Slack\Command\Handler\SlackCommandHandlerInterface;
use App\Slack\Command\SlackCommand;
use App\Slack\Command\SubCommand;
use App\Slack\Response\Interaction\Factory\GenericFailureResponseFactory;
use App\Slack\Response\Interaction\SlackInteractionResponse;
use App\Slack\Response\PrivateMessage\NoResponse;
use App\Slack\Surface\Component\ModalSurface;
use App\Slack\Surface\Factory\Modal\ConfigurationModalFactory;
use App\Slack\Surface\Service\ModalService;
use PHPUnit\Framework\Attributes\Test;

class ConfigureCommandHandlerTest extends AbstractCommandHandlerTestCase
{
    #[Test]
    public function itShouldSetGenericFailureResponseIfWorkspaceNotFound(): void
    {
        $workspaceRepository = $this->createMock(WorkspaceRepositoryInterface::class);
        $workspaceRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['slackId' => $teamId = 'teamId'])
            ->willReturn(null);

        $genericFailureFactory = $this->createMock(GenericFailureResponseFactory::class);
        $genericFailureFactory->expects($this->once())
            ->method('create')
            ->willReturn($response = $this->createStub(SlackInteractionResponse::class));

        $command = $this->createMock(SlackCommand::class);
        $command->expects($this->once())
            ->method('getTeamId')
            ->willReturn($teamId);

        $command->expects($this->once())
            ->method('setResponse')
            ->with($response);

        $handler = new ConfigureCommandHandler(
            $this->createStub(UserResolver::class),
            $workspaceRepository,
            $this->createStub(ConfigurationModalFactory::class),
            $this->createStub(ModalService::class),
            $genericFailureFactory,
        );

        $handler->handle($command);
    }

    #[Test]
    public function itShouldSetGenericFailureResponseIfModalCouldNotBeCreated(): void
    {
        $workspaceRepository = $this->createMock(WorkspaceRepositoryInterface::class);
        $workspaceRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['slackId' => $teamId = 'teamId'])
            ->willReturn($workspace = $this->createStub(Workspace::class));

        $userResolver = $this->createMock(UserResolver::class);
        $userResolver->expects($this->once())
            ->method('resolve')
            ->with($userId = 'userId', $workspace)
            ->willReturn($user = $this->createStub(User::class));

        $modalFactory = $this->createMock(ConfigurationModalFactory::class);
        $modalFactory->expects($this->once())
            ->method('create')
            ->with($user, $command = $this->createMock(SlackCommand::class))
            ->willReturn(null);

        $genericFailureFactory = $this->createMock(GenericFailureResponseFactory::class);
        $genericFailureFactory->expects($this->once())
            ->method('create')
            ->willReturn($response = $this->createStub(SlackInteractionResponse::class));

        $command->expects($this->once())
            ->method('getUserId')
            ->willReturn($userId);

        $command->expects($this->once())
            ->method('getTeamId')
            ->willReturn($teamId);

        $command->expects($this->once())
            ->method('setResponse')
            ->with($response);

        $handler = new ConfigureCommandHandler(
            $userResolver,
            $workspaceRepository,
            $modalFactory,
            $this->createStub(ModalService::class),
            $genericFailureFactory,
        );

        $handler->handle($command);
    }

    #[Test]
    public function itShouldCreateModal(): void
    {
        $workspaceRepository = $this->createMock(WorkspaceRepositoryInterface::class);
        $workspaceRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['slackId' => $teamId = 'teamId'])
            ->willReturn($workspace = $this->createStub(Workspace::class));

        $userResolver = $this->createMock(UserResolver::class);
        $userResolver->expects($this->once())
            ->method('resolve')
            ->with($userId = 'userId', $workspace)
            ->willReturn($user = $this->createStub(User::class));

        $modalFactory = $this->createMock(ConfigurationModalFactory::class);
        $modalFactory->expects($this->once())
            ->method('create')
            ->with($user, $command = $this->createMock(SlackCommand::class))
            ->willReturn($modal = $this->createStub(ModalSurface::class));

        $modalService = $this->createMock(ModalService::class);
        $modalService->expects($this->once())
            ->method('createModal')
            ->with($modal, $workspace);

        $genericFailureFactory = $this->createMock(GenericFailureResponseFactory::class);
        $genericFailureFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $command->expects($this->once())
            ->method('getUserId')
            ->willReturn($userId);

        $command->expects($this->once())
            ->method('getTeamId')
            ->willReturn($teamId);

        $command->expects($this->once())
            ->method('setResponse')
            ->willReturnCallback(function ($response) {
                $this->assertInstanceOf(NoResponse::class, $response);
            });

        $handler = new ConfigureCommandHandler(
            $userResolver,
            $workspaceRepository,
            $modalFactory,
            $modalService,
            $genericFailureFactory,
        );

        $handler->handle($command);
    }

    protected function getSupportedCommand(): Command
    {
        return Command::BBQ;
    }

    protected function getSupportedSubCommand(): SubCommand
    {
        return SubCommand::CONFIGURE;
    }

    protected function getUnsupportedCommand(): Command
    {
        return Command::BBQ_ADMIN;
    }

    protected function getUnsupportedSubCommand(): SubCommand
    {
        return SubCommand::EDIT_QUEUE;
    }

    protected function getHandler(): SlackCommandHandlerInterface
    {
        return new ConfigureCommandHandler(
            $this->createStub(UserResolver::class),
            $this->createStub(WorkspaceRepositoryInterface::class),
            $this->createStub(ConfigurationModalFactory::class),
            $this->createStub(ModalService::class),
            $this->createStub(GenericFailureResponseFactory::class),
        );
    }
}

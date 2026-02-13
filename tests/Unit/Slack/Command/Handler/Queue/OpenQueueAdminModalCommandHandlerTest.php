<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Command\Handler\Queue;

use App\Entity\Administrator;
use App\Entity\Queue;
use App\Entity\Workspace;
use App\Repository\QueueRepositoryInterface;
use App\Slack\Command\Command;
use App\Slack\Command\CommandArgument;
use App\Slack\Command\Handler\Queue\OpenQueueAdminModalCommandHandler;
use App\Slack\Command\Handler\SlackCommandHandlerInterface;
use App\Slack\Command\SlackCommand;
use App\Slack\Command\SubCommand;
use App\Slack\Common\Component\Exception\UnauthorisedUserException;
use App\Slack\Common\Validator\AuthorisationValidator;
use App\Slack\Response\Interaction\Factory\Administrator\UnauthorisedResponseFactory;
use App\Slack\Response\Interaction\Factory\GenericFailureResponseFactory;
use App\Slack\Response\Interaction\Factory\Queue\Common\UnrecognisedQueueResponseFactory;
use App\Slack\Response\Interaction\SlackInteractionResponse;
use App\Slack\Surface\Component\ModalSurface;
use App\Slack\Surface\Factory\Modal\EditQueueModalFactory;
use App\Slack\Surface\Service\ModalService;
use App\Tests\Unit\Slack\Command\Handler\AbstractCommandHandlerTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(OpenQueueAdminModalCommandHandler::class)]
class OpenQueueAdminModalCommandHandlerTest extends AbstractCommandHandlerTestCase
{
    #[Test]
    public function itShouldCreateUnauthorisedResponseIfAuthorisationValidatorFails(): void
    {
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

        $exception = $this->createStub(UnauthorisedUserException::class);

        $validator = $this->createMock(AuthorisationValidator::class);
        $validator->expects($this->once())
            ->method('validate')
            ->with($commandType, $userId, $teamId)
            ->willThrowException($exception);

        $response = $this->createStub(SlackInteractionResponse::class);

        $unauthorisedResponseFactory = $this->createMock(UnauthorisedResponseFactory::class);
        $unauthorisedResponseFactory->expects($this->once())
            ->method('create')
            ->willReturn($response);

        $command->expects($this->once())
            ->method('setResponse')
            ->with($response);

        $handler = new OpenQueueAdminModalCommandHandler(
            $this->createStub(QueueRepositoryInterface::class),
            $this->createStub(UnrecognisedQueueResponseFactory::class),
            $this->createStub(ModalService::class),
            $validator,
            $unauthorisedResponseFactory,
            $this->createStub(EditQueueModalFactory::class),
            $this->createStub(GenericFailureResponseFactory::class),
        );

        $handler->handle($command);
    }

    #[Test]
    public function itShouldCreateUnrecognisedQueueResponseIfQueueNotFound(): void
    {
        $response = $this->createStub(SlackInteractionResponse::class);

        $command = $this->createMock(SlackCommand::class);
        $command->expects($this->once())
            ->method('getArgumentString')
            ->with(CommandArgument::QUEUE)
            ->willReturn($queueName = 'queueName');

        $command->expects($this->exactly(2))
            ->method('getTeamId')
            ->willReturn($teamId = 'teamId');

        $command->expects($this->once())
            ->method('getUserId')
            ->willReturn($userId = 'userId');

        $command->expects($this->once())
            ->method('getCommand')
            ->willReturn($commandType = Command::BBQ_ADMIN);

        $command->expects($this->once())
            ->method('setResponse')
            ->with($response);

        $repository = $this->createMock(QueueRepositoryInterface::class);
        $repository->expects($this->once())
            ->method('findOneByNameAndTeamId')
            ->with($queueName, $teamId)
            ->willReturn(null);

        $responseFactory = $this->createMock(UnrecognisedQueueResponseFactory::class);
        $responseFactory->expects($this->once())
            ->method('create')
            ->with($queueName, $teamId, withActions: false)
            ->willReturn($response);

        $modalFactory = $this->createMock(EditQueueModalFactory::class);
        $modalFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $modalService = $this->createMock(ModalService::class);
        $modalService->expects($this->never())
            ->method('createModal')
            ->withAnyParameters();

        $validator = $this->createMock(AuthorisationValidator::class);
        $validator->expects($this->once())
            ->method('validate')
            ->with($commandType, $userId, $teamId)
            ->willReturn($administrator = $this->createStub(Administrator::class));

        $command->expects($this->once())
            ->method('setAdministrator')
            ->with($administrator);

        $handler = new OpenQueueAdminModalCommandHandler(
            $repository,
            $responseFactory,
            $modalService,
            $validator,
            $this->createStub(UnauthorisedResponseFactory::class),
            $modalFactory,
            $this->createStub(GenericFailureResponseFactory::class),
        );

        $handler->handle($command);
    }

    #[Test]
    public function itShouldReturnEarlyIfModalFactoryReturnsNull(): void
    {
        $genericFailureResponseFactory = $this->createMock(GenericFailureResponseFactory::class);
        $genericFailureResponseFactory->expects($this->once())
            ->method('create')
            ->willReturn($response = $this->createStub(SlackInteractionResponse::class));

        $command = $this->createMock(SlackCommand::class);
        $command->expects($this->once())
            ->method('getArgumentString')
            ->with(CommandArgument::QUEUE)
            ->willReturn($queueName = 'queueName');

        $command->expects($this->exactly(2))
            ->method('getTeamId')
            ->willReturn($teamId = 'teamId');

        $command->expects($this->once())
            ->method('setResponse')
            ->with($response);

        $command->expects($this->once())
            ->method('getUserId')
            ->willReturn($userId = 'userId');

        $command->expects($this->once())
            ->method('getCommand')
            ->willReturn($commandType = Command::BBQ_ADMIN);

        $queue = $this->createMock(Queue::class);
        $queue->expects($this->never())
            ->method('getWorkspace');

        $repository = $this->createMock(QueueRepositoryInterface::class);
        $repository->expects($this->once())
            ->method('findOneByNameAndTeamId')
            ->with($queueName, $teamId)
            ->willReturn($queue);

        $responseFactory = $this->createMock(UnrecognisedQueueResponseFactory::class);
        $responseFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $modalFactory = $this->createMock(EditQueueModalFactory::class);
        $modalFactory->expects($this->once())
            ->method('create')
            ->with($queue, $command)
            ->willReturn(null);

        $modalService = $this->createMock(ModalService::class);
        $modalService->expects($this->never())
            ->method('createModal')
            ->withAnyParameters();

        $validator = $this->createMock(AuthorisationValidator::class);
        $validator->expects($this->once())
            ->method('validate')
            ->with($commandType, $userId, $teamId)
            ->willReturn($administrator = $this->createStub(Administrator::class));

        $command->expects($this->once())
            ->method('setAdministrator')
            ->with($administrator);

        $handler = new OpenQueueAdminModalCommandHandler(
            $repository,
            $responseFactory,
            $modalService,
            $validator,
            $this->createStub(UnauthorisedResponseFactory::class),
            $modalFactory,
            $genericFailureResponseFactory,
        );

        $handler->handle($command);
    }

    #[Test]
    public function itShouldCreateQueueModalIfQueueExists(): void
    {
        $command = $this->createMock(SlackCommand::class);
        $command->expects($this->once())
            ->method('getArgumentString')
            ->with(CommandArgument::QUEUE)
            ->willReturn($queueName = 'queueName');

        $command->expects($this->exactly(2))
            ->method('getTeamId')
            ->willReturn($teamId = 'teamId');

        $command->expects($this->never())
            ->method('setResponse')
            ->withAnyParameters();

        $command->expects($this->once())
            ->method('getUserId')
            ->willReturn($userId = 'userId');

        $command->expects($this->once())
            ->method('getCommand')
            ->willReturn($commandType = Command::BBQ_ADMIN);

        $queue = $this->createMock(Queue::class);
        $queue->expects($this->once())
            ->method('getWorkspace')
            ->willReturn($workspace = $this->createStub(Workspace::class));

        $repository = $this->createMock(QueueRepositoryInterface::class);
        $repository->expects($this->once())
            ->method('findOneByNameAndTeamId')
            ->with($queueName, $teamId)
            ->willReturn($queue);

        $responseFactory = $this->createMock(UnrecognisedQueueResponseFactory::class);
        $responseFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $modalFactory = $this->createMock(EditQueueModalFactory::class);
        $modalFactory->expects($this->once())
            ->method('create')
            ->with($queue, $command)
            ->willReturn($modal = $this->createStub(ModalSurface::class));

        $modalService = $this->createMock(ModalService::class);
        $modalService->expects($this->once())
            ->method('createModal')
            ->with($modal, $workspace);

        $validator = $this->createMock(AuthorisationValidator::class);
        $validator->expects($this->once())
            ->method('validate')
            ->with($commandType, $userId, $teamId)
            ->willReturn($administrator = $this->createStub(Administrator::class));

        $command->expects($this->once())
            ->method('setAdministrator')
            ->with($administrator);

        $handler = new OpenQueueAdminModalCommandHandler(
            $repository,
            $responseFactory,
            $modalService,
            $validator,
            $this->createStub(UnauthorisedResponseFactory::class),
            $modalFactory,
            $this->createStub(GenericFailureResponseFactory::class),
        );

        $handler->handle($command);
    }

    protected function getSupportedCommand(): Command
    {
        return Command::BBQ_ADMIN;
    }

    protected function getSupportedSubCommand(): SubCommand
    {
        return SubCommand::EDIT_QUEUE;
    }

    protected function getUnsupportedCommand(): Command
    {
        return Command::BBQ;
    }

    protected function getUnsupportedSubCommand(): SubCommand
    {
        return SubCommand::POP_QUEUE;
    }

    protected function getHandler(): SlackCommandHandlerInterface
    {
        return new OpenQueueAdminModalCommandHandler(
            $this->createStub(QueueRepositoryInterface::class),
            $this->createStub(UnrecognisedQueueResponseFactory::class),
            $this->createStub(ModalService::class),
            $this->createStub(AuthorisationValidator::class),
            $this->createStub(UnauthorisedResponseFactory::class),
            $this->createStub(EditQueueModalFactory::class),
            $this->createStub(GenericFailureResponseFactory::class),
        );
    }
}

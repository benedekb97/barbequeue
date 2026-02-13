<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Command\Handler\Queue;

use App\Entity\Queue;
use App\Entity\QueuedUser;
use App\Entity\Workspace;
use App\Service\Queue\Exception\DeploymentInformationRequiredException;
use App\Service\Queue\Exception\QueueNotFoundException;
use App\Service\Queue\Exception\UnableToJoinQueueException;
use App\Service\Queue\Join\JoinQueueContext;
use App\Service\Queue\Join\JoinQueueHandler;
use App\Slack\Command\Command;
use App\Slack\Command\CommandArgument;
use App\Slack\Command\Handler\Queue\JoinQueueCommandHandler;
use App\Slack\Command\Handler\SlackCommandHandlerInterface;
use App\Slack\Command\SlackCommand;
use App\Slack\Command\SubCommand;
use App\Slack\Response\Interaction\Factory\GenericFailureResponseFactory;
use App\Slack\Response\Interaction\Factory\Queue\Common\UnrecognisedQueueResponseFactory;
use App\Slack\Response\Interaction\Factory\Queue\Join\QueueJoinedResponseFactory;
use App\Slack\Response\Interaction\Factory\Queue\Join\UnableToJoinQueueResponseFactory;
use App\Slack\Response\Interaction\SlackInteractionResponse;
use App\Slack\Response\PrivateMessage\NoResponse;
use App\Slack\Surface\Component\ModalSurface;
use App\Slack\Surface\Factory\Modal\JoinQueueModalFactory;
use App\Slack\Surface\Service\ModalService;
use App\Tests\Unit\Slack\Command\Handler\AbstractCommandHandlerTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(JoinQueueCommandHandler::class)]
class JoinQueueCommandHandlerTest extends AbstractCommandHandlerTestCase
{
    #[Test]
    public function itShouldCreateUnrecognisedQueueResponseIfQueueNotFoundExceptionThrown(): void
    {
        $command = $this->createMock(SlackCommand::class);
        $command->expects($this->once())
            ->method('getArgumentString')
            ->with(CommandArgument::QUEUE)
            ->willReturn($queueName = 'queueName');

        $command->expects($this->once())
            ->method('getTeamId')
            ->willReturn($teamId = 'teamId');

        $command->expects($this->once())
            ->method('getUserId')
            ->willReturn($userId = 'userId');

        $command->expects($this->once())
            ->method('getUserName')
            ->willReturn($userName = 'userName');

        $command->expects($this->once())
            ->method('getOptionalArgumentInteger')
            ->with(CommandArgument::TIME)
            ->willReturn(null);

        $exception = $this->createMock(QueueNotFoundException::class);
        $exception->expects($this->once())
            ->method('getQueueName')
            ->willReturn($queueName);

        $exception->expects($this->once())
            ->method('getTeamId')
            ->willReturn($teamId);

        $exception->expects($this->once())
            ->method('getUserId')
            ->willReturn($userId);

        $handler = $this->createMock(JoinQueueHandler::class);
        $handler->expects($this->once())
            ->method('handle')
            ->willThrowException($exception);

        $response = $this->createStub(SlackInteractionResponse::class);

        $command->expects($this->once())
            ->method('setResponse')
            ->with($response);

        $unrecognisedQueueResponseFactory = $this->createMock(UnrecognisedQueueResponseFactory::class);
        $unrecognisedQueueResponseFactory->expects($this->once())
            ->method('create')
            ->with($queueName, $teamId, $userId)
            ->willReturn($response);

        $queueJoinedResponseFactory = $this->createMock(QueueJoinedResponseFactory::class);
        $queueJoinedResponseFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $unableToJoinQueueResponseFactory = $this->createMock(UnableToJoinQueueResponseFactory::class);
        $unableToJoinQueueResponseFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $genericFailureResponseFactory = $this->createMock(GenericFailureResponseFactory::class);
        $genericFailureResponseFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $handler = new JoinQueueCommandHandler(
            $unrecognisedQueueResponseFactory,
            $unableToJoinQueueResponseFactory,
            $queueJoinedResponseFactory,
            $genericFailureResponseFactory,
            $this->createStub(JoinQueueModalFactory::class),
            $this->createStub(ModalService::class),
            $handler,
        );

        $handler->handle($command);
    }

    #[Test]
    public function itShouldCreateUnableToJoinQueueResponseIfUnableToJoinQueueExceptionThrown(): void
    {
        $command = $this->createMock(SlackCommand::class);
        $command->expects($this->once())
            ->method('getArgumentString')
            ->with(CommandArgument::QUEUE)
            ->willReturn($queueName = 'queueName');

        $command->expects($this->once())
            ->method('getTeamId')
            ->willReturn($teamId = 'teamId');

        $command->expects($this->once())
            ->method('getUserId')
            ->willReturn($userId = 'userId');

        $command->expects($this->once())
            ->method('getOptionalArgumentInteger')
            ->with(CommandArgument::TIME)
            ->willReturn(null);

        $queue = $this->createStub(Queue::class);

        $exception = $this->createMock(UnableToJoinQueueException::class);
        $exception->expects($this->once())
            ->method('getQueue')
            ->willReturn($queue);

        $handler = $this->createMock(JoinQueueHandler::class);
        $handler->expects($this->once())
            ->method('handle')
            ->willThrowException($exception);

        $response = $this->createStub(SlackInteractionResponse::class);

        $command->expects($this->once())
            ->method('setResponse')
            ->with($response);

        $unrecognisedQueueResponseFactory = $this->createMock(UnrecognisedQueueResponseFactory::class);
        $unrecognisedQueueResponseFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $queueJoinedResponseFactory = $this->createMock(QueueJoinedResponseFactory::class);
        $queueJoinedResponseFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $unableToJoinQueueResponseFactory = $this->createMock(UnableToJoinQueueResponseFactory::class);
        $unableToJoinQueueResponseFactory->expects($this->once())
            ->method('create')
            ->with($queue)
            ->willReturn($response);

        $genericFailureResponseFactory = $this->createMock(GenericFailureResponseFactory::class);
        $genericFailureResponseFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $handler = new JoinQueueCommandHandler(
            $unrecognisedQueueResponseFactory,
            $unableToJoinQueueResponseFactory,
            $queueJoinedResponseFactory,
            $genericFailureResponseFactory,
            $this->createStub(JoinQueueModalFactory::class),
            $this->createStub(ModalService::class),
            $handler,
        );

        $handler->handle($command);
    }

    #[Test]
    public function itShouldCreateJoinQueueModalIfDeploymentInformationRequiredExceptionThrown(): void
    {
        $command = $this->createMock(SlackCommand::class);
        $command->expects($this->once())
            ->method('getArgumentString')
            ->with(CommandArgument::QUEUE)
            ->willReturn($queueName = 'queueName');

        $command->expects($this->once())
            ->method('getTeamId')
            ->willReturn($teamId = 'teamId');

        $command->expects($this->once())
            ->method('getUserId')
            ->willReturn($userId = 'userId');

        $command->expects($this->once())
            ->method('getOptionalArgumentInteger')
            ->with(CommandArgument::TIME)
            ->willReturn(null);

        $queue = $this->createMock(Queue::class);
        $queue->expects($this->once())
            ->method('getWorkspace')
            ->willReturn($workspace = $this->createStub(Workspace::class));

        $exception = $this->createMock(DeploymentInformationRequiredException::class);
        $exception->expects($this->once())
            ->method('getQueue')
            ->willReturn($queue);

        $handler = $this->createMock(JoinQueueHandler::class);
        $handler->expects($this->once())
            ->method('handle')
            ->willThrowException($exception);

        $command->expects($this->once())
            ->method('setResponse')
            ->willReturnCallback(function ($response) {
                $this->assertInstanceOf(NoResponse::class, $response);
            });

        $unrecognisedQueueResponseFactory = $this->createMock(UnrecognisedQueueResponseFactory::class);
        $unrecognisedQueueResponseFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $queueJoinedResponseFactory = $this->createMock(QueueJoinedResponseFactory::class);
        $queueJoinedResponseFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $unableToJoinQueueResponseFactory = $this->createMock(UnableToJoinQueueResponseFactory::class);
        $unableToJoinQueueResponseFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $genericFailureResponseFactory = $this->createMock(GenericFailureResponseFactory::class);
        $genericFailureResponseFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $modalFactory = $this->createMock(JoinQueueModalFactory::class);
        $modalFactory->expects($this->once())
            ->method('create')
            ->with($queue, $command)
            ->willReturn($modal = $this->createStub(ModalSurface::class));

        $modalService = $this->createMock(ModalService::class);
        $modalService->expects($this->once())
            ->method('createModal')
            ->with($modal, $workspace);

        $handler = new JoinQueueCommandHandler(
            $unrecognisedQueueResponseFactory,
            $unableToJoinQueueResponseFactory,
            $queueJoinedResponseFactory,
            $genericFailureResponseFactory,
            $modalFactory,
            $modalService,
            $handler,
        );

        $handler->handle($command);
    }

    #[Test]
    public function itShouldCreateQueueJoinedResponseFactoryIfQueueJoinedSuccessfully(): void
    {
        $command = $this->createMock(SlackCommand::class);
        $command->expects($this->once())
            ->method('getArgumentString')
            ->with(CommandArgument::QUEUE)
            ->willReturn($queueName = 'queueName');

        $command->expects($this->once())
            ->method('getTeamId')
            ->willReturn($teamId = 'teamId');

        $command->expects($this->once())
            ->method('getUserId')
            ->willReturn($userId = 'userId');

        $command->expects($this->once())
            ->method('getUserName')
            ->willReturn($userName = 'userName');

        $command->expects($this->once())
            ->method('getOptionalArgumentInteger')
            ->with(CommandArgument::TIME)
            ->willReturn($time = 50);

        $queuedUser = $this->createStub(QueuedUser::class);

        $handler = $this->createMock(JoinQueueHandler::class);
        $handler->expects($this->once())
            ->method('handle')
            ->willReturnCallback(function ($context) use ($queueName, $teamId, $userId, $userName, $time, $queuedUser) {
                $this->assertInstanceOf(JoinQueueContext::class, $context);

                $this->assertSame($queueName, $context->getQueueIdentifier());
                $this->assertSame($teamId, $context->getTeamId());
                $this->assertSame($userId, $context->getUserId());
                $this->assertSame($userName, $context->getUserName());
                $this->assertSame($time, $context->getRequiredMinutes());

                $context->setQueuedUser($queuedUser);
            });

        $response = $this->createStub(SlackInteractionResponse::class);

        $command->expects($this->once())
            ->method('setResponse')
            ->with($response);

        $unrecognisedQueueResponseFactory = $this->createMock(UnrecognisedQueueResponseFactory::class);
        $unrecognisedQueueResponseFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $queueJoinedResponseFactory = $this->createMock(QueueJoinedResponseFactory::class);
        $queueJoinedResponseFactory->expects($this->once())
            ->method('create')
            ->with($queuedUser)
            ->willReturn($response);

        $unableToJoinQueueResponseFactory = $this->createMock(UnableToJoinQueueResponseFactory::class);
        $unableToJoinQueueResponseFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $genericFailureResponseFactory = $this->createMock(GenericFailureResponseFactory::class);
        $genericFailureResponseFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $handler = new JoinQueueCommandHandler(
            $unrecognisedQueueResponseFactory,
            $unableToJoinQueueResponseFactory,
            $queueJoinedResponseFactory,
            $genericFailureResponseFactory,
            $this->createStub(JoinQueueModalFactory::class),
            $this->createStub(ModalService::class),
            $handler,
        );

        $handler->handle($command);
    }

    protected function getSupportedCommand(): Command
    {
        return Command::BBQ;
    }

    protected function getSupportedSubCommand(): SubCommand
    {
        return SubCommand::JOIN;
    }

    protected function getUnsupportedCommand(): Command
    {
        return Command::BBQ_ADMIN;
    }

    protected function getUnsupportedSubCommand(): SubCommand
    {
        return SubCommand::LEAVE;
    }

    protected function getHandler(): SlackCommandHandlerInterface
    {
        return new JoinQueueCommandHandler(
            $this->createStub(UnrecognisedQueueResponseFactory::class),
            $this->createStub(UnableToJoinQueueResponseFactory::class),
            $this->createStub(QueueJoinedResponseFactory::class),
            $this->createStub(GenericFailureResponseFactory::class),
            $this->createStub(JoinQueueModalFactory::class),
            $this->createStub(ModalService::class),
            $this->createStub(JoinQueueHandler::class),
        );
    }
}

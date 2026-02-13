<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Command\Handler\Queue;

use App\Entity\Queue;
use App\Entity\Workspace;
use App\Service\Queue\Exception\LeaveQueueInformationRequiredException;
use App\Service\Queue\Exception\QueueNotFoundException;
use App\Service\Queue\Exception\UnableToLeaveQueueException;
use App\Service\Queue\Leave\LeaveQueueContext;
use App\Service\Queue\Leave\LeaveQueueHandler;
use App\Slack\Command\Command;
use App\Slack\Command\CommandArgument;
use App\Slack\Command\Handler\Queue\LeaveQueueCommandHandler;
use App\Slack\Command\Handler\SlackCommandHandlerInterface;
use App\Slack\Command\SlackCommand;
use App\Slack\Command\SubCommand;
use App\Slack\Response\Interaction\Factory\GenericFailureResponseFactory;
use App\Slack\Response\Interaction\Factory\Queue\Common\UnrecognisedQueueResponseFactory;
use App\Slack\Response\Interaction\Factory\Queue\Leave\QueueLeftResponseFactory;
use App\Slack\Response\Interaction\Factory\Queue\Leave\UnableToLeaveQueueResponseFactory;
use App\Slack\Response\Interaction\SlackInteractionResponse;
use App\Slack\Response\PrivateMessage\NoResponse;
use App\Slack\Surface\Component\ModalSurface;
use App\Slack\Surface\Factory\Modal\LeaveQueueModalFactory;
use App\Slack\Surface\Service\ModalService;
use App\Tests\Unit\Slack\Command\Handler\AbstractCommandHandlerTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(LeaveQueueCommandHandler::class)]
class LeaveQueueCommandHandlerTest extends AbstractCommandHandlerTestCase
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

        $handler = $this->createMock(LeaveQueueHandler::class);
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

        $queueLeftResponseFactory = $this->createMock(QueueLeftResponseFactory::class);
        $queueLeftResponseFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $unableToLeaveQueueResponseFactory = $this->createMock(UnableToLeaveQueueResponseFactory::class);
        $unableToLeaveQueueResponseFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $genericFailureResponseFactory = $this->createMock(GenericFailureResponseFactory::class);
        $genericFailureResponseFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $handler = new LeaveQueueCommandHandler(
            $unrecognisedQueueResponseFactory,
            $unableToLeaveQueueResponseFactory,
            $queueLeftResponseFactory,
            $genericFailureResponseFactory,
            $handler,
            $this->createStub(LeaveQueueModalFactory::class),
            $this->createStub(ModalService::class),
        );

        $handler->handle($command);
    }

    #[Test]
    public function itShouldCreateUnableToLeaveQueueResponseIfUnableToLeaveQueueExceptionThrown(): void
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

        $queue = $this->createStub(Queue::class);

        $exception = $this->createMock(UnableToLeaveQueueException::class);
        $exception->expects($this->once())
            ->method('getQueue')
            ->willReturn($queue);

        $handler = $this->createMock(LeaveQueueHandler::class);
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

        $queueLeftResponseFactory = $this->createMock(QueueLeftResponseFactory::class);
        $queueLeftResponseFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $unableToLeaveQueueResponseFactory = $this->createMock(UnableToLeaveQueueResponseFactory::class);
        $unableToLeaveQueueResponseFactory->expects($this->once())
            ->method('create')
            ->with($queue)
            ->willReturn($response);

        $genericFailureResponseFactory = $this->createMock(GenericFailureResponseFactory::class);
        $genericFailureResponseFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $handler = new LeaveQueueCommandHandler(
            $unrecognisedQueueResponseFactory,
            $unableToLeaveQueueResponseFactory,
            $queueLeftResponseFactory,
            $genericFailureResponseFactory,
            $handler,
            $this->createStub(LeaveQueueModalFactory::class),
            $this->createStub(ModalService::class),
        );

        $handler->handle($command);
    }

    #[Test]
    public function itShouldCreateLeaveQueueModalIfLeaveQueueInformationRequiredExceptionThrown(): void
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

        $queue = $this->createMock(Queue::class);
        $queue->expects($this->once())
            ->method('getWorkspace')
            ->willReturn($workspace = $this->createStub(Workspace::class));

        $exception = $this->createMock(LeaveQueueInformationRequiredException::class);
        $exception->expects($this->once())
            ->method('getQueue')
            ->willReturn($queue);

        $handler = $this->createMock(LeaveQueueHandler::class);
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

        $queueLeftResponseFactory = $this->createMock(QueueLeftResponseFactory::class);
        $queueLeftResponseFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $unableToLeaveQueueResponseFactory = $this->createMock(UnableToLeaveQueueResponseFactory::class);
        $unableToLeaveQueueResponseFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $genericFailureResponseFactory = $this->createMock(GenericFailureResponseFactory::class);
        $genericFailureResponseFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $modalFactory = $this->createMock(LeaveQueueModalFactory::class);
        $modalFactory->expects($this->once())
            ->method('create')
            ->with($queue, $command)
            ->willReturn($modal = $this->createStub(ModalSurface::class));

        $modalService = $this->createMock(ModalService::class);
        $modalService->expects($this->once())
            ->method('createModal')
            ->with($modal, $workspace);

        $handler = new LeaveQueueCommandHandler(
            $unrecognisedQueueResponseFactory,
            $unableToLeaveQueueResponseFactory,
            $queueLeftResponseFactory,
            $genericFailureResponseFactory,
            $handler,
            $modalFactory,
            $modalService,
        );

        $handler->handle($command);
    }

    #[Test]
    public function itShouldCreateQueueLeftResponseIfQueueLeftSuccessfully(): void
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

        $queue = $this->createStub(Queue::class);

        $handler = $this->createMock(LeaveQueueHandler::class);
        $handler->expects($this->once())
            ->method('handle')
            ->willReturnCallback(function ($context) use ($queue, $queueName, $teamId, $userId) {
                $this->assertInstanceOf(LeaveQueueContext::class, $context);

                $this->assertSame($queueName, $context->getQueueIdentifier());
                $this->assertSame($teamId, $context->getTeamId());
                $this->assertSame($userId, $context->getUserId());

                $context->setQueue($queue);
            });

        $response = $this->createStub(SlackInteractionResponse::class);

        $command->expects($this->once())
            ->method('setResponse')
            ->with($response);

        $unrecognisedQueueResponseFactory = $this->createMock(UnrecognisedQueueResponseFactory::class);
        $unrecognisedQueueResponseFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $queueLeftResponseFactory = $this->createMock(QueueLeftResponseFactory::class);
        $queueLeftResponseFactory->expects($this->once())
            ->method('create')
            ->with($queue, $userId)
            ->willReturn($response);

        $unableToLeaveQueueResponseFactory = $this->createMock(UnableToLeaveQueueResponseFactory::class);
        $unableToLeaveQueueResponseFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $genericFailureResponseFactory = $this->createMock(GenericFailureResponseFactory::class);
        $genericFailureResponseFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $handler = new LeaveQueueCommandHandler(
            $unrecognisedQueueResponseFactory,
            $unableToLeaveQueueResponseFactory,
            $queueLeftResponseFactory,
            $genericFailureResponseFactory,
            $handler,
            $this->createStub(LeaveQueueModalFactory::class),
            $this->createStub(ModalService::class),
        );

        $handler->handle($command);
    }

    protected function getSupportedCommand(): Command
    {
        return Command::BBQ;
    }

    protected function getSupportedSubCommand(): SubCommand
    {
        return SubCommand::LEAVE;
    }

    protected function getUnsupportedCommand(): Command
    {
        return Command::BBQ_ADMIN;
    }

    protected function getUnsupportedSubCommand(): SubCommand
    {
        return SubCommand::JOIN;
    }

    protected function getHandler(): SlackCommandHandlerInterface
    {
        return new LeaveQueueCommandHandler(
            $this->createStub(UnrecognisedQueueResponseFactory::class),
            $this->createStub(UnableToLeaveQueueResponseFactory::class),
            $this->createStub(QueueLeftResponseFactory::class),
            $this->createStub(GenericFailureResponseFactory::class),
            $this->createStub(LeaveQueueHandler::class),
            $this->createStub(LeaveQueueModalFactory::class),
            $this->createStub(ModalService::class),
        );
    }
}

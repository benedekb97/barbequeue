<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Command\Handler\Queue;

use App\Entity\Queue;
use App\Entity\Workspace;
use App\Service\Queue\Exception\PopQueueInformationRequiredException;
use App\Service\Queue\Exception\QueueNotFoundException;
use App\Service\Queue\Pop\PopQueueContext;
use App\Service\Queue\Pop\PopQueueHandler;
use App\Slack\Command\Command;
use App\Slack\Command\CommandArgument;
use App\Slack\Command\Handler\Queue\PopQueueCommandHandler;
use App\Slack\Command\Handler\SlackCommandHandlerInterface;
use App\Slack\Command\SlackCommand;
use App\Slack\Command\SubCommand;
use App\Slack\Common\Validator\AuthorisationValidator;
use App\Slack\Response\Interaction\Factory\Administrator\UnauthorisedResponseFactory;
use App\Slack\Response\Interaction\Factory\Queue\Common\UnrecognisedQueueResponseFactory;
use App\Slack\Response\Interaction\Factory\Queue\QueuePoppedResponseFactory;
use App\Slack\Response\Interaction\SlackInteractionResponse;
use App\Slack\Response\PrivateMessage\NoResponse;
use App\Slack\Surface\Component\ModalSurface;
use App\Slack\Surface\Factory\Modal\PopQueueModalFactory;
use App\Slack\Surface\Service\ModalService;
use App\Tests\Unit\Slack\Command\Handler\AbstractCommandHandlerTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(PopQueueCommandHandler::class)]
class PopQueueCommandHandlerTest extends AbstractCommandHandlerTestCase
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

        $handler = $this->createMock(PopQueueHandler::class);
        $handler->expects($this->once())
            ->method('handle')
            ->willThrowException($exception);

        $response = $this->createStub(SlackInteractionResponse::class);

        $responseFactory = $this->createMock(UnrecognisedQueueResponseFactory::class);
        $responseFactory->expects($this->once())
            ->method('create')
            ->with($queueName, $teamId, null, false)
            ->willReturn($response);

        $command->expects($this->once())
            ->method('setResponse')
            ->with($response);

        $queuePoppedResponseFactory = $this->createMock(QueuePoppedResponseFactory::class);
        $queuePoppedResponseFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $handler = new PopQueueCommandHandler(
            $responseFactory,
            $queuePoppedResponseFactory,
            $this->createStub(AuthorisationValidator::class),
            $this->createStub(UnauthorisedResponseFactory::class),
            $handler,
            $this->createStub(PopQueueModalFactory::class),
            $this->createStub(ModalService::class),
        );

        $handler->run($command);
    }

    #[Test]
    public function itShouldCreatePopQueueModalIfPopQueueInformationRequiredExceptionThrown(): void
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

        $exception = $this->createMock(PopQueueInformationRequiredException::class);
        $exception->expects($this->once())
            ->method('getQueue')
            ->willReturn($queue = $this->createMock(Queue::class));

        $queue->expects($this->once())
            ->method('getWorkspace')
            ->willReturn($workspace = $this->createStub(Workspace::class));

        $handler = $this->createMock(PopQueueHandler::class);
        $handler->expects($this->once())
            ->method('handle')
            ->willThrowException($exception);

        $responseFactory = $this->createMock(UnrecognisedQueueResponseFactory::class);
        $responseFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $command->expects($this->once())
            ->method('setResponse')
            ->willReturnCallback(function ($response) {
                $this->assertInstanceOf(NoResponse::class, $response);
            });

        $modalFactory = $this->createMock(PopQueueModalFactory::class);
        $modalFactory->expects($this->once())
            ->method('create')
            ->with($queue, $command)
            ->willReturn($modal = $this->createStub(ModalSurface::class));

        $modalService = $this->createMock(ModalService::class);
        $modalService->expects($this->once())
            ->method('createModal')
            ->with($modal, $workspace);

        $queuePoppedResponseFactory = $this->createMock(QueuePoppedResponseFactory::class);
        $queuePoppedResponseFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $handler = new PopQueueCommandHandler(
            $responseFactory,
            $queuePoppedResponseFactory,
            $this->createStub(AuthorisationValidator::class),
            $this->createStub(UnauthorisedResponseFactory::class),
            $handler,
            $modalFactory,
            $modalService,
        );

        $handler->run($command);
    }

    #[Test]
    public function itShouldCreateQueuePoppedResponseIfQueuePoppedSuccessfully(): void
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

        $handler = $this->createMock(PopQueueHandler::class);
        $handler->expects($this->once())
            ->method('handle')
            ->willReturnCallback(function ($context) use ($queue, $teamId, $queueName, $userId) {
                $this->assertInstanceOf(PopQueueContext::class, $context);

                $this->assertSame($queueName, $context->getQueueIdentifier());
                $this->assertSame($teamId, $context->getTeamId());
                $this->assertSame($userId, $context->getUserId());

                $context->setQueue($queue);
            });

        $response = $this->createStub(SlackInteractionResponse::class);

        $unrecognisedQueueResponseFactory = $this->createMock(UnrecognisedQueueResponseFactory::class);
        $unrecognisedQueueResponseFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $command->expects($this->once())
            ->method('setResponse')
            ->with($response);

        $queuePoppedResponseFactory = $this->createMock(QueuePoppedResponseFactory::class);
        $queuePoppedResponseFactory->expects($this->once())
            ->method('create')
            ->with($queue)
            ->willReturn($response);

        $handler = new PopQueueCommandHandler(
            $unrecognisedQueueResponseFactory,
            $queuePoppedResponseFactory,
            $this->createStub(AuthorisationValidator::class),
            $this->createStub(UnauthorisedResponseFactory::class),
            $handler,
            $this->createStub(PopQueueModalFactory::class),
            $this->createStub(ModalService::class),
        );

        $handler->run($command);
    }

    protected function getSupportedCommand(): Command
    {
        return Command::BBQ_ADMIN;
    }

    protected function getSupportedSubCommand(): SubCommand
    {
        return SubCommand::POP_QUEUE;
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
        return new PopQueueCommandHandler(
            $this->createStub(UnrecognisedQueueResponseFactory::class),
            $this->createStub(QueuePoppedResponseFactory::class),
            $this->createStub(AuthorisationValidator::class),
            $this->createStub(UnauthorisedResponseFactory::class),
            $this->createStub(PopQueueHandler::class),
            $this->createStub(PopQueueModalFactory::class),
            $this->createStub(ModalService::class),
        );
    }
}

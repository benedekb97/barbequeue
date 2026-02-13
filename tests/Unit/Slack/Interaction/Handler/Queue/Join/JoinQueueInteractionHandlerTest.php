<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Interaction\Handler\Queue\Join;

use App\Entity\Queue;
use App\Entity\QueuedUser;
use App\Entity\Workspace;
use App\Service\Queue\Exception\DeploymentInformationRequiredException;
use App\Service\Queue\Exception\QueueNotFoundException;
use App\Service\Queue\Exception\UnableToJoinQueueException;
use App\Service\Queue\Join\JoinQueueContext;
use App\Service\Queue\Join\JoinQueueHandler;
use App\Slack\Interaction\Handler\Queue\Join\JoinQueueInteractionHandler;
use App\Slack\Interaction\Handler\SlackInteractionHandlerInterface;
use App\Slack\Interaction\Interaction;
use App\Slack\Interaction\InteractionType;
use App\Slack\Interaction\SlackInteraction;
use App\Slack\Response\Interaction\Factory\GenericFailureResponseFactory;
use App\Slack\Response\Interaction\Factory\Queue\Common\UnrecognisedQueueResponseFactory;
use App\Slack\Response\Interaction\Factory\Queue\Join\QueueJoinedResponseFactory;
use App\Slack\Response\Interaction\Factory\Queue\Join\UnableToJoinQueueResponseFactory;
use App\Slack\Response\Interaction\SlackInteractionResponse;
use App\Slack\Response\PrivateMessage\NoResponse;
use App\Slack\Surface\Component\ModalSurface;
use App\Slack\Surface\Factory\Modal\JoinQueueModalFactory;
use App\Slack\Surface\Service\ModalService;
use App\Tests\Unit\Slack\Interaction\Handler\AbstractInteractionHandlerTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(JoinQueueInteractionHandler::class)]
class JoinQueueInteractionHandlerTest extends AbstractInteractionHandlerTestCase
{
    #[Test]
    public function itShouldCreateUnrecognisedQueueResponseIfQueueNotFoundExceptionThrown(): void
    {
        $interaction = $this->createMock(SlackInteraction::class);
        $interaction->expects($this->once())
            ->method('getValue')
            ->willReturn($value = 'value');

        $interaction->expects($this->once())
            ->method('getTeamId')
            ->willReturn($teamId = 'teamId');

        $interaction->expects($this->once())
            ->method('getUserId')
            ->willReturn($userId = 'userId');

        $response = $this->createStub(SlackInteractionResponse::class);

        $interaction->expects($this->once())
            ->method('setResponse')
            ->with($response);

        $exception = $this->createMock(QueueNotFoundException::class);
        $exception->expects($this->once())
            ->method('getQueueName')
            ->willReturn($value);

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

        $queueJoinedResponseFactory = $this->createMock(QueueJoinedResponseFactory::class);
        $queueJoinedResponseFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $unrecognisedQueueResponseFactory = $this->createMock(UnrecognisedQueueResponseFactory::class);
        $unrecognisedQueueResponseFactory->expects($this->once())
            ->method('create')
            ->with($value, $teamId, $userId)
            ->willReturn($response);

        $unableToJoinQueueResponseFactory = $this->createMock(UnableToJoinQueueResponseFactory::class);
        $unableToJoinQueueResponseFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $genericFailureResponseFactory = $this->createMock(GenericFailureResponseFactory::class);
        $genericFailureResponseFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $handler = new JoinQueueInteractionHandler(
            $queueJoinedResponseFactory,
            $unableToJoinQueueResponseFactory,
            $unrecognisedQueueResponseFactory,
            $genericFailureResponseFactory,
            $this->createStub(JoinQueueModalFactory::class),
            $this->createStub(ModalService::class),
            $handler,
        );

        $handler->handle($interaction);
    }

    #[Test]
    public function itShouldCreateUnableToJoinQueueResponseIfUnableToJoinQueueExceptionThrown(): void
    {
        $interaction = $this->createMock(SlackInteraction::class);
        $interaction->expects($this->once())
            ->method('getValue')
            ->willReturn($value = 'value');

        $interaction->expects($this->once())
            ->method('getTeamId')
            ->willReturn($teamId = 'teamId');

        $interaction->expects($this->once())
            ->method('getUserId')
            ->willReturn($userId = 'userId');

        $response = $this->createStub(SlackInteractionResponse::class);

        $interaction->expects($this->once())
            ->method('setResponse')
            ->with($response);

        $queue = $this->createStub(Queue::class);

        $exception = $this->createMock(UnableToJoinQueueException::class);
        $exception->expects($this->once())
            ->method('getQueue')
            ->willReturn($queue);

        $handler = $this->createMock(JoinQueueHandler::class);
        $handler->expects($this->once())
            ->method('handle')
            ->willThrowException($exception);

        $queueJoinedResponseFactory = $this->createMock(QueueJoinedResponseFactory::class);
        $queueJoinedResponseFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $unrecognisedQueueResponseFactory = $this->createMock(UnrecognisedQueueResponseFactory::class);
        $unrecognisedQueueResponseFactory->expects($this->never())
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

        $handler = new JoinQueueInteractionHandler(
            $queueJoinedResponseFactory,
            $unableToJoinQueueResponseFactory,
            $unrecognisedQueueResponseFactory,
            $genericFailureResponseFactory,
            $this->createStub(JoinQueueModalFactory::class),
            $this->createStub(ModalService::class),
            $handler,
        );

        $handler->handle($interaction);
    }

    #[Test]
    public function itShouldOpenJoinQueueModalIfDeploymentInformationRequiredExceptionThrown(): void
    {
        $interaction = $this->createMock(SlackInteraction::class);
        $interaction->expects($this->once())
            ->method('getValue')
            ->willReturn($value = 'value');

        $interaction->expects($this->once())
            ->method('getTeamId')
            ->willReturn($teamId = 'teamId');

        $interaction->expects($this->once())
            ->method('getUserId')
            ->willReturn($userId = 'userId');

        $interaction->expects($this->once())
            ->method('setResponse')
            ->willReturnCallback(function ($response) {
                $this->assertInstanceOf(NoResponse::class, $response);
            });

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

        $queueJoinedResponseFactory = $this->createMock(QueueJoinedResponseFactory::class);
        $queueJoinedResponseFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $unrecognisedQueueResponseFactory = $this->createMock(UnrecognisedQueueResponseFactory::class);
        $unrecognisedQueueResponseFactory->expects($this->never())
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

        $joinQueueModalFactory = $this->createMock(JoinQueueModalFactory::class);
        $joinQueueModalFactory->expects($this->once())
            ->method('create')
            ->with($queue, $interaction)
            ->willReturn($modal = $this->createStub(ModalSurface::class));

        $modalService = $this->createMock(ModalService::class);
        $modalService->expects($this->once())
            ->method('createModal')
            ->with($modal, $workspace);

        $handler = new JoinQueueInteractionHandler(
            $queueJoinedResponseFactory,
            $unableToJoinQueueResponseFactory,
            $unrecognisedQueueResponseFactory,
            $genericFailureResponseFactory,
            $joinQueueModalFactory,
            $modalService,
            $handler,
        );

        $handler->handle($interaction);
    }

    #[Test]
    public function itShouldCreateQueueJoinedResponseIfQueueJoinedSuccessfully(): void
    {
        $interaction = $this->createMock(SlackInteraction::class);
        $interaction->expects($this->once())
            ->method('getValue')
            ->willReturn($value = 'value');

        $interaction->expects($this->once())
            ->method('getTeamId')
            ->willReturn($teamId = 'teamId');

        $interaction->expects($this->once())
            ->method('getUserId')
            ->willReturn($userId = 'userId');

        $response = $this->createStub(SlackInteractionResponse::class);

        $interaction->expects($this->once())
            ->method('setResponse')
            ->with($response);

        $queuedUser = $this->createStub(QueuedUser::class);

        $handler = $this->createMock(JoinQueueHandler::class);
        $handler->expects($this->once())
            ->method('handle')
            ->willReturnCallback(function ($context) use ($queuedUser, $value, $userId, $teamId) {
                $this->assertInstanceOf(JoinQueueContext::class, $context);
                $this->assertEquals($value, $context->getQueueIdentifier());
                $this->assertEquals($teamId, $context->getTeamId());
                $this->assertEquals($userId, $context->getUserId());

                $context->setQueuedUser($queuedUser);
            });

        $queueJoinedResponseFactory = $this->createMock(QueueJoinedResponseFactory::class);
        $queueJoinedResponseFactory->expects($this->once())
            ->method('create')
            ->with($queuedUser)
            ->willReturn($response);

        $unrecognisedQueueResponseFactory = $this->createMock(UnrecognisedQueueResponseFactory::class);
        $unrecognisedQueueResponseFactory->expects($this->never())
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

        $handler = new JoinQueueInteractionHandler(
            $queueJoinedResponseFactory,
            $unableToJoinQueueResponseFactory,
            $unrecognisedQueueResponseFactory,
            $genericFailureResponseFactory,
            $this->createStub(JoinQueueModalFactory::class),
            $this->createStub(ModalService::class),
            $handler,
        );

        $handler->handle($interaction);
    }

    protected function getSupportedInteraction(): Interaction
    {
        return Interaction::JOIN_QUEUE;
    }

    protected function getUnsupportedInteraction(): Interaction
    {
        return Interaction::EDIT_QUEUE;
    }

    protected function getUnsupportedInteractionType(): InteractionType
    {
        return InteractionType::VIEW_SUBMISSION;
    }

    protected function getSupportedInteractionType(): InteractionType
    {
        return InteractionType::BLOCK_ACTIONS;
    }

    protected function getHandler(): SlackInteractionHandlerInterface
    {
        return new JoinQueueInteractionHandler(
            $this->createStub(QueueJoinedResponseFactory::class),
            $this->createStub(UnableToJoinQueueResponseFactory::class),
            $this->createStub(UnrecognisedQueueResponseFactory::class),
            $this->createStub(GenericFailureResponseFactory::class),
            $this->createStub(JoinQueueModalFactory::class),
            $this->createStub(ModalService::class),
            $this->createStub(JoinQueueHandler::class),
        );
    }
}

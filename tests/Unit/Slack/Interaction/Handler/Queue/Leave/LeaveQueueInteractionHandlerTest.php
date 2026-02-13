<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Interaction\Handler\Queue\Leave;

use App\Entity\Queue;
use App\Service\Queue\Exception\LeaveQueueInformationRequiredException;
use App\Service\Queue\Exception\QueueNotFoundException;
use App\Service\Queue\Exception\UnableToLeaveQueueException;
use App\Service\Queue\Leave\LeaveQueueContext;
use App\Service\Queue\Leave\LeaveQueueHandler;
use App\Slack\Interaction\Handler\Queue\Leave\LeaveQueueInteractionHandler;
use App\Slack\Interaction\Handler\SlackInteractionHandlerInterface;
use App\Slack\Interaction\Interaction;
use App\Slack\Interaction\InteractionType;
use App\Slack\Interaction\SlackInteraction;
use App\Slack\Response\Interaction\Factory\GenericFailureResponseFactory;
use App\Slack\Response\Interaction\Factory\Queue\Common\UnrecognisedQueueResponseFactory;
use App\Slack\Response\Interaction\Factory\Queue\Leave\QueueLeftResponseFactory;
use App\Slack\Response\Interaction\Factory\Queue\Leave\UnableToLeaveQueueResponseFactory;
use App\Slack\Response\Interaction\SlackInteractionResponse;
use App\Slack\Response\PrivateMessage\NoResponse;
use App\Slack\Surface\Component\ModalSurface;
use App\Slack\Surface\Factory\Modal\LeaveQueueModalFactory;
use App\Slack\Surface\Service\ModalService;
use App\Tests\Unit\Slack\Interaction\Handler\AbstractInteractionHandlerTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Psr\Log\LoggerInterface;

#[CoversClass(LeaveQueueInteractionHandler::class)]
class LeaveQueueInteractionHandlerTest extends AbstractInteractionHandlerTestCase
{
    #[Test]
    public function itShouldCreateUnrecognisedQueueResponseIfQueueNotFoundExceptionThrown(): void
    {
        $response = $this->createStub(SlackInteractionResponse::class);

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

        $handler = $this->createMock(LeaveQueueHandler::class);
        $handler->expects($this->once())
            ->method('handle')
            ->willThrowException($exception);

        $queueLeftResponseFactory = $this->createMock(QueueLeftResponseFactory::class);
        $queueLeftResponseFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $unrecognisedQueueResponseFactory = $this->createMock(UnrecognisedQueueResponseFactory::class);
        $unrecognisedQueueResponseFactory->expects($this->once())
            ->method('create')
            ->with($value, $teamId, $userId)
            ->willReturn($response);

        $unableToLeaveQueueResponseFactory = $this->createMock(UnableToLeaveQueueResponseFactory::class);
        $unableToLeaveQueueResponseFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $genericFailureResponseFactory = $this->createMock(GenericFailureResponseFactory::class);
        $genericFailureResponseFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $handler = new LeaveQueueInteractionHandler(
            $unrecognisedQueueResponseFactory,
            $unableToLeaveQueueResponseFactory,
            $queueLeftResponseFactory,
            $genericFailureResponseFactory,
            $handler,
            $this->createStub(LeaveQueueModalFactory::class),
            $this->createStub(ModalService::class),
            $this->createStub(LoggerInterface::class),
        );

        $handler->handle($interaction);
    }

    #[Test]
    public function itShouldCreateUnableToLeaveQueueResponseIfUnableToLeaveQueueExceptionThrown(): void
    {
        $response = $this->createStub(SlackInteractionResponse::class);

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
            ->with($response);

        $queue = $this->createStub(Queue::class);

        $exception = $this->createMock(UnableToLeaveQueueException::class);
        $exception->expects($this->once())
            ->method('getQueue')
            ->willReturn($queue);

        $handler = $this->createMock(LeaveQueueHandler::class);
        $handler->expects($this->once())
            ->method('handle')
            ->willThrowException($exception);

        $queueLeftResponseFactory = $this->createMock(QueueLeftResponseFactory::class);
        $queueLeftResponseFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $unrecognisedQueueResponseFactory = $this->createMock(UnrecognisedQueueResponseFactory::class);
        $unrecognisedQueueResponseFactory->expects($this->never())
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

        $handler = new LeaveQueueInteractionHandler(
            $unrecognisedQueueResponseFactory,
            $unableToLeaveQueueResponseFactory,
            $queueLeftResponseFactory,
            $genericFailureResponseFactory,
            $handler,
            $this->createStub(LeaveQueueModalFactory::class),
            $this->createStub(ModalService::class),
            $this->createStub(LoggerInterface::class),
        );

        $handler->handle($interaction);
    }

    #[Test]
    public function itShouldCreateQueueLeftResponseIfQueueLeftSuccessfully(): void
    {
        $response = $this->createStub(SlackInteractionResponse::class);

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
            ->with($response);

        $queue = $this->createStub(Queue::class);

        $handler = $this->createMock(LeaveQueueHandler::class);
        $handler->expects($this->once())
            ->method('handle')
            ->willReturnCallback(function ($context) use ($queue, $userId, $teamId, $value) {
                $this->assertInstanceOf(LeaveQueueContext::class, $context);

                $this->assertSame($userId, $context->getUserId());
                $this->assertSame($value, $context->getQueueIdentifier());
                $this->assertSame($teamId, $context->getTeamId());

                $context->setQueue($queue);
            });

        $queueLeftResponseFactory = $this->createMock(QueueLeftResponseFactory::class);
        $queueLeftResponseFactory->expects($this->once())
            ->method('create')
            ->with($queue, $userId)
            ->willReturn($response);

        $unrecognisedQueueResponseFactory = $this->createMock(UnrecognisedQueueResponseFactory::class);
        $unrecognisedQueueResponseFactory->expects($this->never())
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

        $handler = new LeaveQueueInteractionHandler(
            $unrecognisedQueueResponseFactory,
            $unableToLeaveQueueResponseFactory,
            $queueLeftResponseFactory,
            $genericFailureResponseFactory,
            $handler,
            $this->createStub(LeaveQueueModalFactory::class),
            $this->createStub(ModalService::class),
            $this->createStub(LoggerInterface::class),
        );

        $handler->handle($interaction);
    }

    #[Test]
    public function itShouldCreateLeaveQueueModalIfLeaveQueueInformationRequiredExceptionThrown(): void
    {
        $response = $this->createStub(SlackInteractionResponse::class);

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
            ->willReturn(null);

        $exception = $this->createMock(LeaveQueueInformationRequiredException::class);
        $exception->expects($this->once())
            ->method('getQueue')
            ->willReturn($queue);

        $handler = $this->createMock(LeaveQueueHandler::class);
        $handler->expects($this->once())
            ->method('handle')
            ->willThrowException($exception);

        $queueLeftResponseFactory = $this->createMock(QueueLeftResponseFactory::class);
        $queueLeftResponseFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $unrecognisedQueueResponseFactory = $this->createMock(UnrecognisedQueueResponseFactory::class);
        $unrecognisedQueueResponseFactory->expects($this->never())
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
            ->with($queue, $interaction)
            ->willReturn($modal = $this->createStub(ModalSurface::class));

        $modalService = $this->createMock(ModalService::class);
        $modalService->expects($this->once())
            ->method('createModal')
            ->with($modal, null);

        $handler = new LeaveQueueInteractionHandler(
            $unrecognisedQueueResponseFactory,
            $unableToLeaveQueueResponseFactory,
            $queueLeftResponseFactory,
            $genericFailureResponseFactory,
            $handler,
            $modalFactory,
            $modalService,
            $this->createStub(LoggerInterface::class),
        );

        $handler->handle($interaction);
    }

    protected function getSupportedInteraction(): Interaction
    {
        return Interaction::LEAVE_QUEUE;
    }

    protected function getUnsupportedInteraction(): Interaction
    {
        return Interaction::JOIN_QUEUE;
    }

    protected function getSupportedInteractionType(): InteractionType
    {
        return InteractionType::BLOCK_ACTIONS;
    }

    protected function getUnsupportedInteractionType(): InteractionType
    {
        return InteractionType::VIEW_SUBMISSION;
    }

    protected function getHandler(): SlackInteractionHandlerInterface
    {
        return new LeaveQueueInteractionHandler(
            $this->createStub(UnrecognisedQueueResponseFactory::class),
            $this->createStub(UnableToLeaveQueueResponseFactory::class),
            $this->createStub(QueueLeftResponseFactory::class),
            $this->createStub(GenericFailureResponseFactory::class),
            $this->createStub(LeaveQueueHandler::class),
            $this->createStub(LeaveQueueModalFactory::class),
            $this->createStub(ModalService::class),
            $this->createStub(LoggerInterface::class),
        );
    }
}

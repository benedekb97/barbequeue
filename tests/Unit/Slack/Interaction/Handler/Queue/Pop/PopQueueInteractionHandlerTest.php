<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Interaction\Handler\Queue\Pop;

use App\Entity\Queue;
use App\Service\Queue\Exception\PopQueueInformationRequiredException;
use App\Service\Queue\Exception\QueueNotFoundException;
use App\Service\Queue\Pop\PopQueueContext;
use App\Service\Queue\Pop\PopQueueHandler;
use App\Slack\Common\Validator\AuthorisationValidator;
use App\Slack\Interaction\Handler\Queue\Pop\PopQueueInteractionHandler;
use App\Slack\Interaction\Handler\SlackInteractionHandlerInterface;
use App\Slack\Interaction\Interaction;
use App\Slack\Interaction\InteractionType;
use App\Slack\Interaction\SlackInteraction;
use App\Slack\Response\Interaction\Factory\Administrator\UnauthorisedResponseFactory;
use App\Slack\Response\Interaction\Factory\Queue\Common\UnrecognisedQueueResponseFactory;
use App\Slack\Response\Interaction\Factory\Queue\QueuePoppedResponseFactory;
use App\Slack\Response\Interaction\SlackInteractionResponse;
use App\Slack\Response\PrivateMessage\NoResponse;
use App\Slack\Surface\Component\ModalSurface;
use App\Slack\Surface\Factory\Modal\PopQueueModalFactory;
use App\Slack\Surface\Service\ModalService;
use App\Tests\Unit\Slack\Interaction\Handler\AbstractInteractionHandlerTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;

#[CoversClass(PopQueueInteractionHandler::class)]
class PopQueueInteractionHandlerTest extends AbstractInteractionHandlerTestCase
{
    #[Test]
    public function itShouldCreateUnrecognisedQueueResponseIfQueueNotFoundExceptionThrown(): void
    {
        $interaction = $this->createMock(SlackInteraction::class);
        $interaction->expects($this->once())
            ->method('getValue')
            ->willReturn($queueName = 'queueName');

        $interaction->expects($this->once())
            ->method('getTeamId')
            ->willReturn($teamId = 'teamId');

        $interaction->expects($this->once())
            ->method('getUserId')
            ->withAnyParameters();

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

        $poppedResponseFactory = $this->createMock(QueuePoppedResponseFactory::class);
        $poppedResponseFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects($this->never())
            ->method('dispatch')
            ->withAnyParameters();

        $unrecognisedQueueResponseFactory = $this->createMock(UnrecognisedQueueResponseFactory::class);
        $unrecognisedQueueResponseFactory->expects($this->once())
            ->method('create')
            ->with($queueName, $teamId, null, false)
            ->willReturn($response = $this->createStub(SlackInteractionResponse::class));

        $interaction->expects($this->once())
            ->method('setResponse')
            ->with($response);

        $handler = new PopQueueInteractionHandler(
            $this->createStub(AuthorisationValidator::class),
            $this->createStub(UnauthorisedResponseFactory::class),
            $unrecognisedQueueResponseFactory,
            $poppedResponseFactory,
            $handler,
            $this->createStub(PopQueueModalFactory::class),
            $this->createStub(ModalService::class),
            $this->createStub(LoggerInterface::class),
        );

        $handler->run($interaction);
    }

    #[Test]
    public function itShouldCreatePopQueueModalIfPopQueueInformationRequiredExceptionThrown(): void
    {
        $interaction = $this->createMock(SlackInteraction::class);
        $interaction->expects($this->once())
            ->method('getValue')
            ->willReturn('queueName');

        $interaction->expects($this->once())
            ->method('getTeamId')
            ->willReturn('teamId');

        $interaction->expects($this->once())
            ->method('getUserId')
            ->willReturn('userId');

        $queue = $this->createMock(Queue::class);
        $queue->expects($this->once())
            ->method('getWorkspace')
            ->willReturn(null);

        $exception = $this->createMock(PopQueueInformationRequiredException::class);
        $exception->expects($this->once())
            ->method('getQueue')
            ->willReturn($queue);

        $handler = $this->createMock(PopQueueHandler::class);
        $handler->expects($this->once())
            ->method('handle')
            ->willThrowException($exception);

        $poppedResponseFactory = $this->createMock(QueuePoppedResponseFactory::class);
        $poppedResponseFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects($this->never())
            ->method('dispatch')
            ->withAnyParameters();

        $unrecognisedQueueResponseFactory = $this->createMock(UnrecognisedQueueResponseFactory::class);
        $unrecognisedQueueResponseFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $interaction->expects($this->once())
            ->method('setResponse')
            ->willReturnCallback(function ($response) {
                $this->assertInstanceOf(NoResponse::class, $response);
            });

        $modalFactory = $this->createMock(PopQueueModalFactory::class);
        $modalFactory->expects($this->once())
            ->method('create')
            ->with($queue, $interaction)
            ->willReturn($modal = $this->createStub(ModalSurface::class));

        $modalService = $this->createMock(ModalService::class);
        $modalService->expects($this->once())
            ->method('createModal')
            ->with($modal, null);

        $handler = new PopQueueInteractionHandler(
            $this->createStub(AuthorisationValidator::class),
            $this->createStub(UnauthorisedResponseFactory::class),
            $unrecognisedQueueResponseFactory,
            $poppedResponseFactory,
            $handler,
            $modalFactory,
            $modalService,
            $this->createStub(LoggerInterface::class),
        );

        $handler->run($interaction);
    }

    #[Test]
    public function itShouldCreateQueuePoppedResponse(): void
    {
        $interaction = $this->createMock(SlackInteraction::class);
        $interaction->expects($this->once())
            ->method('getValue')
            ->willReturn($queueName = 'queueName');

        $interaction->expects($this->once())
            ->method('getTeamId')
            ->willReturn($teamId = 'teamId');

        $interaction->expects($this->once())
            ->method('getUserId')
            ->willReturn($userId = 'userId');

        $queue = $this->createStub(Queue::class);

        $handler = $this->createMock(PopQueueHandler::class);
        $handler->expects($this->once())
            ->method('handle')
            ->willReturnCallback(function ($context) use ($queue, $queueName, $teamId, $userId) {
                $this->assertInstanceof(PopQueueContext::class, $context);

                $this->assertSame($queueName, $context->getQueueIdentifier());
                $this->assertSame($teamId, $context->getTeamId());
                $this->assertSame($userId, $context->getUserId());

                $context->setQueue($queue);
            });

        $poppedResponseFactory = $this->createMock(QueuePoppedResponseFactory::class);
        $poppedResponseFactory->expects($this->once())
            ->method('create')
            ->with($queue)
            ->willReturn($response = $this->createStub(SlackInteractionResponse::class));

        $unrecognisedQueueResponseFactory = $this->createMock(UnrecognisedQueueResponseFactory::class);
        $unrecognisedQueueResponseFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $interaction->expects($this->once())
            ->method('setResponse')
            ->with($response);

        $handler = new PopQueueInteractionHandler(
            $this->createStub(AuthorisationValidator::class),
            $this->createStub(UnauthorisedResponseFactory::class),
            $unrecognisedQueueResponseFactory,
            $poppedResponseFactory,
            $handler,
            $this->createStub(PopQueueModalFactory::class),
            $this->createStub(ModalService::class),
            $this->createStub(LoggerInterface::class),
        );

        $handler->run($interaction);
    }

    protected function getSupportedInteraction(): Interaction
    {
        return Interaction::POP_QUEUE_ACTION;
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
        return new PopQueueInteractionHandler(
            $this->createStub(AuthorisationValidator::class),
            $this->createStub(UnauthorisedResponseFactory::class),
            $this->createStub(UnrecognisedQueueResponseFactory::class),
            $this->createStub(QueuePoppedResponseFactory::class),
            $this->createStub(PopQueueHandler::class),
            $this->createStub(PopQueueModalFactory::class),
            $this->createStub(ModalService::class),
            $this->createStub(LoggerInterface::class),
        );
    }
}

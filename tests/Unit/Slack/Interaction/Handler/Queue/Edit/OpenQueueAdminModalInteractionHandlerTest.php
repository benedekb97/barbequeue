<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Interaction\Handler\Queue\Edit;

use App\Entity\Administrator;
use App\Entity\Queue;
use App\Entity\Workspace;
use App\Repository\QueueRepositoryInterface;
use App\Slack\Common\Component\Exception\UnauthorisedUserException;
use App\Slack\Common\Validator\AuthorisationValidator;
use App\Slack\Interaction\Handler\Queue\Edit\OpenQueueAdminModalInteractionHandler;
use App\Slack\Interaction\Handler\SlackInteractionHandlerInterface;
use App\Slack\Interaction\Interaction;
use App\Slack\Interaction\InteractionType;
use App\Slack\Interaction\SlackInteraction;
use App\Slack\Response\Interaction\Factory\Administrator\UnauthorisedResponseFactory;
use App\Slack\Response\Interaction\Factory\Queue\Common\UnrecognisedQueueResponseFactory;
use App\Slack\Response\Interaction\SlackInteractionResponse;
use App\Slack\Response\PrivateMessage\NoResponse;
use App\Slack\Surface\Component\ModalSurface;
use App\Slack\Surface\Factory\Modal\EditQueueModalFactory;
use App\Slack\Surface\Service\ModalService;
use App\Tests\Unit\Slack\Interaction\Handler\AbstractInteractionHandlerTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(OpenQueueAdminModalInteractionHandler::class)]
class OpenQueueAdminModalInteractionHandlerTest extends AbstractInteractionHandlerTestCase
{
    #[Test]
    public function itShouldCreateUnauthorisedResponseIfAuthorisationValidatorFails(): void
    {
        $viewSubmission = $this->createMock(SlackInteraction::class);
        $viewSubmission->expects($this->once())
            ->method('getInteraction')
            ->willReturn($interaction = Interaction::EDIT_QUEUE);

        $viewSubmission->expects($this->once())
            ->method('getUserId')
            ->willReturn($userId = 'userId');

        $viewSubmission->expects($this->once())
            ->method('getTeamId')
            ->willReturn($teamId = 'teamId');

        $validator = $this->createMock(AuthorisationValidator::class);
        $validator->expects($this->once())
            ->method('validate')
            ->with($interaction, $userId, $teamId)
            ->willThrowException($this->createStub(UnauthorisedUserException::class));

        $unauthorisedResponseFactory = $this->createMock(UnauthorisedResponseFactory::class);
        $unauthorisedResponseFactory->expects($this->once())
            ->method('create')
            ->willReturn($response = $this->createStub(SlackInteractionResponse::class));

        $viewSubmission->expects($this->once())
            ->method('setResponse')
            ->with($response);

        $handler = new OpenQueueAdminModalInteractionHandler(
            $this->createStub(QueueRepositoryInterface::class),
            $this->createStub(UnrecognisedQueueResponseFactory::class),
            $this->createStub(ModalService::class),
            $validator,
            $unauthorisedResponseFactory,
            $this->createStub(EditQueueModalFactory::class),
        );

        $handler->handle($viewSubmission);
    }

    #[Test]
    public function itShouldCreateUnrecognisedQueueResponseIfQueueNotFound(): void
    {
        $response = $this->createStub(SlackInteractionResponse::class);

        $interaction = $this->createMock(SlackInteraction::class);
        $interaction->expects($this->once())
            ->method('getValue')
            ->willReturn($queueId = '1');

        $interaction->expects($this->once())
            ->method('setResponse')
            ->with($response);

        $repository = $this->createMock(QueueRepositoryInterface::class);
        $repository->expects($this->once())
            ->method('find')
            ->with((int) $queueId)
            ->willReturn(null);

        $interaction->expects($this->once())
            ->method('getUserId')
            ->willReturn($userId = 'userId');

        $interaction->expects($this->once())
            ->method('getTeamId')
            ->willReturn($teamId = 'teamId');

        $interaction->expects($this->once())
            ->method('getInteraction')
            ->willReturn($interactionType = Interaction::EDIT_QUEUE_ACTION);

        $validator = $this->createMock(AuthorisationValidator::class);
        $validator->expects($this->once())
            ->method('validate')
            ->with($interactionType, $userId, $teamId)
            ->willReturn($administrator = $this->createStub(Administrator::class));

        $interaction->expects($this->once())
            ->method('setAdministrator')
            ->with($administrator);

        $unrecognisedQueueResponseFactory = $this->createMock(UnrecognisedQueueResponseFactory::class);
        $unrecognisedQueueResponseFactory->expects($this->once())
            ->method('create')
            ->with('', '', withActions: false)
            ->willReturn($response);

        $modalFactory = $this->createMock(EditQueueModalFactory::class);
        $modalFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $modalService = $this->createMock(ModalService::class);
        $modalService->expects($this->never())
            ->method('createModal')
            ->withAnyParameters();

        $handler = new OpenQueueAdminModalInteractionHandler(
            $repository,
            $unrecognisedQueueResponseFactory,
            $modalService,
            $validator,
            $this->createStub(UnauthorisedResponseFactory::class),
            $modalFactory,
        );

        $handler->handle($interaction);
    }

    #[Test]
    public function itShouldSetNoResponseOnInteractionIfModalCreatedSuccessfully(): void
    {
        $queue = $this->createMock(Queue::class);
        $queue->expects($this->once())
            ->method('getWorkspace')
            ->willReturn($workspace = $this->createStub(Workspace::class));

        $interaction = $this->createMock(SlackInteraction::class);
        $interaction->expects($this->once())
            ->method('getValue')
            ->willReturn($queueId = '1');

        $interaction->expects($this->once())
            ->method('setResponse')
            ->willReturnCallback(function ($argument) {
                $this->assertInstanceOf(NoResponse::class, $argument);
            });

        $repository = $this->createMock(QueueRepositoryInterface::class);
        $repository->expects($this->once())
            ->method('find')
            ->with((int) $queueId)
            ->willReturn($queue);

        $unrecognisedQueueResponseFactory = $this->createMock(UnrecognisedQueueResponseFactory::class);
        $unrecognisedQueueResponseFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $interaction->expects($this->once())
            ->method('getUserId')
            ->willReturn($userId = 'userId');

        $interaction->expects($this->once())
            ->method('getTeamId')
            ->willReturn($teamId = 'teamId');

        $interaction->expects($this->once())
            ->method('getInteraction')
            ->willReturn($interactionType = Interaction::EDIT_QUEUE_ACTION);

        $validator = $this->createMock(AuthorisationValidator::class);
        $validator->expects($this->once())
            ->method('validate')
            ->with($interactionType, $userId, $teamId)
            ->willReturn($administrator = $this->createStub(Administrator::class));

        $interaction->expects($this->once())
            ->method('setAdministrator')
            ->with($administrator);

        $modalFactory = $this->createMock(EditQueueModalFactory::class);
        $modalFactory->expects($this->once())
            ->method('create')
            ->with($queue, $interaction)
            ->willReturn($modal = $this->createStub(ModalSurface::class));

        $modalService = $this->createMock(ModalService::class);
        $modalService->expects($this->once())
            ->method('createModal')
            ->with($modal, $workspace);

        $handler = new OpenQueueAdminModalInteractionHandler(
            $repository,
            $unrecognisedQueueResponseFactory,
            $modalService,
            $validator,
            $this->createStub(UnauthorisedResponseFactory::class),
            $modalFactory,
        );

        $handler->handle($interaction);
    }

    protected function getSupportedInteraction(): Interaction
    {
        return Interaction::EDIT_QUEUE_ACTION;
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
        return new OpenQueueAdminModalInteractionHandler(
            $this->createStub(QueueRepositoryInterface::class),
            $this->createStub(UnrecognisedQueueResponseFactory::class),
            $this->createStub(ModalService::class),
            $this->createStub(AuthorisationValidator::class),
            $this->createStub(UnauthorisedResponseFactory::class),
            $this->createStub(EditQueueModalFactory::class),
        );
    }
}

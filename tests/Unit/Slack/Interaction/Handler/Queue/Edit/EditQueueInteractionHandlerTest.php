<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Interaction\Handler\Queue\Edit;

use App\Entity\Administrator;
use App\Entity\Queue;
use App\Service\Queue\Edit\EditQueueContext;
use App\Service\Queue\Edit\EditQueueHandler;
use App\Slack\Common\Component\Exception\UnauthorisedUserException;
use App\Slack\Common\Validator\AuthorisationValidator;
use App\Slack\Interaction\Handler\Queue\Edit\EditQueueInteractionHandler;
use App\Slack\Interaction\Handler\SlackInteractionHandlerInterface;
use App\Slack\Interaction\Interaction;
use App\Slack\Interaction\InteractionType;
use App\Slack\Interaction\SlackInteraction;
use App\Slack\Interaction\SlackViewSubmission;
use App\Slack\Response\Interaction\Factory\Administrator\UnauthorisedResponseFactory;
use App\Slack\Response\Interaction\Factory\Queue\Edit\QueueEditedResponseFactory;
use App\Slack\Response\Interaction\SlackInteractionResponse;
use App\Slack\Surface\Component\ModalArgument;
use App\Tests\Unit\Slack\Interaction\Handler\AbstractInteractionHandlerTestCase;
use Doctrine\ORM\EntityNotFoundException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Psr\Log\LoggerInterface;

#[CoversClass(EditQueueInteractionHandler::class)]
class EditQueueInteractionHandlerTest extends AbstractInteractionHandlerTestCase
{
    #[Test]
    public function itShouldCreateUnauthorisedResponseIfAuthorisationValidatorFails(): void
    {
        $viewSubmission = $this->createMock(SlackViewSubmission::class);
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

        $handler = new EditQueueInteractionHandler(
            $this->createStub(LoggerInterface::class),
            $this->createStub(QueueEditedResponseFactory::class),
            $validator,
            $unauthorisedResponseFactory,
            $this->createStub(EditQueueHandler::class),
        );

        $handler->handle($viewSubmission);
    }

    #[Test]
    public function itShouldNotHandlePlainSlackInteraction(): void
    {
        $interaction = $this->createMock(SlackInteraction::class);
        $interaction->expects($this->once())
            ->method('getInteraction')
            ->willReturn($interactionType = Interaction::EDIT_QUEUE);

        $interaction->expects($this->once())
            ->method('getUserId')
            ->willReturn($userId = 'userId');

        $interaction->expects($this->once())
            ->method('getTeamId')
            ->willReturn($teamId = 'teamId');

        $validator = $this->createMock(AuthorisationValidator::class);
        $validator->expects($this->once())
            ->method('validate')
            ->with($interactionType, $userId, $teamId)
            ->willReturn($administrator = $this->createStub(Administrator::class));

        $interaction->expects($this->once())
            ->method('setAdministrator')
            ->with($administrator);

        $queueEditedResponseFactory = $this->createMock(QueueEditedResponseFactory::class);
        $queueEditedResponseFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->never())
            ->method('debug')
            ->withAnyParameters();

        $handler = new EditQueueInteractionHandler(
            $logger,
            $queueEditedResponseFactory,
            $validator,
            $this->createStub(UnauthorisedResponseFactory::class),
            $this->createStub(EditQueueHandler::class),
        );

        $handler->handle($interaction);
    }

    #[Test]
    public function itShouldLogDebugMessageIfRequestedQueueNotFound(): void
    {
        $queueId = 1;
        $maximumEntriesPerUser = 1;
        $expiry = null;

        $interaction = $this->createMock(SlackViewSubmission::class);
        $interaction->expects($this->exactly(3))
            ->method('getArgumentInteger')
            ->withAnyParameters()
            ->willReturnCallback(function ($argument) use ($queueId, $maximumEntriesPerUser, $expiry) {
                return match ($argument) {
                    'queue' => $queueId,
                    'maximum_entries_per_user' => $maximumEntriesPerUser,
                    'expiry_minutes' => $expiry,
                    default => null,
                };
            });

        $interaction->expects($this->once())
            ->method('getInteraction')
            ->willReturn($interactionType = Interaction::EDIT_QUEUE);

        $interaction->expects($this->exactly(2))
            ->method('getUserId')
            ->willReturn($userId = 'userId');

        $interaction->expects($this->exactly(2))
            ->method('getTeamId')
            ->willReturn($teamId = 'teamId');

        $validator = $this->createMock(AuthorisationValidator::class);
        $validator->expects($this->once())
            ->method('validate')
            ->with($interactionType, $userId, $teamId)
            ->willReturn($administrator = $this->createStub(Administrator::class));

        $interaction->expects($this->once())
            ->method('setAdministrator')
            ->with($administrator);

        $interaction->expects($this->once())
            ->method('setHandled');

        $exception = new EntityNotFoundException($message = 'message');

        $handler = $this->createMock(EditQueueHandler::class);
        $handler->expects($this->once())
            ->method('handle')
            ->willThrowException($exception);

        $queueEditedResponseFactory = $this->createMock(QueueEditedResponseFactory::class);
        $queueEditedResponseFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('debug')
            ->with($message);

        $handler = new EditQueueInteractionHandler(
            $logger,
            $queueEditedResponseFactory,
            $validator,
            $this->createStub(UnauthorisedResponseFactory::class),
            $handler,
        );

        $handler->handle($interaction);
    }

    #[Test]
    public function itShouldSetInteractionResponseIfQueueEditedSuccessfully(): void
    {
        $queueId = 1;
        $maximumEntriesPerUser = 1;
        $expiry = null;

        $interaction = $this->createMock(SlackViewSubmission::class);
        $interaction->expects($this->once())
            ->method('getArgumentIntArray')
            ->with(ModalArgument::QUEUE_REPOSITORIES->value)
            ->willReturn($repositoryIds = []);

        $interaction->expects($this->once())
            ->method('getArgumentString')
            ->with(ModalArgument::QUEUE_BEHAVIOUR->value)
            ->willReturn($behaviour = 'behaviour');

        $interaction->expects($this->exactly(3))
            ->method('getArgumentInteger')
            ->withAnyParameters()
            ->willReturnCallback(function ($argument) use ($queueId, $maximumEntriesPerUser, $expiry) {
                return match ($argument) {
                    'queue' => $queueId,
                    'maximum_entries_per_user' => $maximumEntriesPerUser,
                    'expiry_minutes' => $expiry,
                    default => null,
                };
            });

        $interaction->expects($this->once())
            ->method('setHandled');

        $interaction->expects($this->exactly(2))
            ->method('getUserId')
            ->willReturn($userId = 'userId');

        $interaction->expects($this->once())
            ->method('getInteraction')
            ->willReturn($interactionType = Interaction::EDIT_QUEUE);

        $interaction->expects($this->exactly(2))
            ->method('getTeamId')
            ->willReturn($teamId = 'teamId');

        $validator = $this->createMock(AuthorisationValidator::class);
        $validator->expects($this->once())
            ->method('validate')
            ->with($interactionType, $userId, $teamId)
            ->willReturn($administrator = $this->createStub(Administrator::class));

        $interaction->expects($this->once())
            ->method('setAdministrator')
            ->with($administrator);

        $queue = $this->createStub(Queue::class);

        $handler = $this->createMock(EditQueueHandler::class);
        $handler->expects($this->once())
            ->method('handle')
            ->willReturnCallback(function ($context) use ($queueId, $teamId, $userId, $maximumEntriesPerUser, $expiry, $repositoryIds, $behaviour, $queue) {
                $this->assertInstanceOf(EditQueueContext::class, $context);

                $this->assertSame($queueId, $context->getQueueIdentifier());
                $this->assertSame($teamId, $context->getTeamId());
                $this->assertSame($userId, $context->getUserId());
                $this->assertSame($maximumEntriesPerUser, $context->getMaximumEntriesPerUser());
                $this->assertSame($expiry, $context->getExpiryMinutes());
                $this->assertSame($repositoryIds, $context->getRepositoryIds());
                $this->assertSame($behaviour, $context->getQueueBehaviour());

                $context->setQueue($queue);
            });

        $response = $this->createStub(SlackInteractionResponse::class);

        $queueEditedResponseFactory = $this->createMock(QueueEditedResponseFactory::class);
        $queueEditedResponseFactory->expects($this->once())
            ->method('create')
            ->with($queue)
            ->willReturn($response);

        $interaction->expects($this->once())
            ->method('setResponse')
            ->with($response);

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->never())
            ->method('debug')
            ->withAnyParameters();

        $handler = new EditQueueInteractionHandler(
            $logger,
            $queueEditedResponseFactory,
            $validator,
            $this->createStub(UnauthorisedResponseFactory::class),
            $handler,
        );

        $handler->handle($interaction);
    }

    protected function getSupportedInteraction(): Interaction
    {
        return Interaction::EDIT_QUEUE;
    }

    protected function getUnsupportedInteraction(): Interaction
    {
        return Interaction::EDIT_QUEUE_ACTION;
    }

    protected function getSupportedInteractionType(): InteractionType
    {
        return InteractionType::VIEW_SUBMISSION;
    }

    protected function getUnsupportedInteractionType(): InteractionType
    {
        return InteractionType::BLOCK_ACTIONS;
    }

    protected function getHandler(): SlackInteractionHandlerInterface
    {
        return new EditQueueInteractionHandler(
            $this->createStub(LoggerInterface::class),
            $this->createStub(QueueEditedResponseFactory::class),
            $this->createStub(AuthorisationValidator::class),
            $this->createStub(UnauthorisedResponseFactory::class),
            $this->createStub(EditQueueHandler::class),
        );
    }
}

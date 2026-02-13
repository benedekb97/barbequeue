<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Interaction\Handler\Queue\Pop;

use App\Service\Queue\Pop\PopQueueHandler;
use App\Slack\Common\Validator\AuthorisationValidator;
use App\Slack\Interaction\Handler\Queue\Pop\PopQueueViewSubmissionHandler;
use App\Slack\Interaction\Handler\SlackInteractionHandlerInterface;
use App\Slack\Interaction\Interaction;
use App\Slack\Interaction\InteractionType;
use App\Slack\Interaction\SlackInteraction;
use App\Slack\Interaction\SlackViewSubmission;
use App\Slack\Response\Interaction\Factory\Administrator\UnauthorisedResponseFactory;
use App\Slack\Response\Interaction\Factory\Queue\Common\UnrecognisedQueueResponseFactory;
use App\Slack\Response\Interaction\Factory\Queue\QueuePoppedResponseFactory;
use App\Slack\Surface\Factory\Modal\PopQueueModalFactory;
use App\Slack\Surface\Service\ModalService;
use App\Tests\Unit\Slack\Interaction\Handler\AbstractInteractionHandlerTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(PopQueueViewSubmissionHandler::class)]
class PopQueueViewSubmissionHandlerTest extends AbstractInteractionHandlerTestCase
{
    #[Test]
    public function itShouldThrowBadMethodCallExceptionIfInteractionNotViewSubmission(): void
    {
        /** @var PopQueueViewSubmissionHandler $handler */
        $handler = $this->getHandler();

        $this->expectException(\BadMethodCallException::class);

        $handler->getContext($this->createStub(SlackInteraction::class));
    }

    #[Test]
    public function itShouldLogWarningIfBadMethodCallExceptionThrown(): void
    {
        $this->expectsWarning('Failed to create context for view submission');

        /** @var PopQueueViewSubmissionHandler $handler */
        $handler = $this->getHandler();

        $handler->run($this->createStub(SlackInteraction::class));
    }

    #[Test]
    public function itShouldCreateContextFromViewSubmission(): void
    {
        $interaction = $this->createMock(SlackViewSubmission::class);
        $interaction->expects($this->once())
            ->method('getArgumentString')
            ->with('queue')
            ->willReturn($queueName = 'queueName');

        $interaction->expects($this->once())
            ->method('getTeamId')
            ->willReturn($teamId = 'teamId');

        $interaction->expects($this->once())
            ->method('getUserId')
            ->willReturn($userId = 'userId');

        $interaction->expects($this->once())
            ->method('getArgumentInteger')
            ->with('queued_user_id')
            ->willReturn($queuedUserId = 1);

        /** @var PopQueueViewSubmissionHandler $handler */
        $handler = $this->getHandler();

        $result = $handler->getContext($interaction);

        $this->assertSame($queueName, $result->getQueueIdentifier());
        $this->assertSame($teamId, $result->getTeamId());
        $this->assertSame($userId, $result->getUserId());
        $this->assertSame($queuedUserId, $result->getQueuedUserId());
    }

    protected function getSupportedInteraction(): Interaction
    {
        return Interaction::POP_QUEUE_ACTION;
    }

    protected function getUnsupportedInteraction(): Interaction
    {
        return Interaction::LEAVE_QUEUE;
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
        return new PopQueueViewSubmissionHandler(
            $this->createStub(AuthorisationValidator::class),
            $this->createStub(UnauthorisedResponseFactory::class),
            $this->createStub(UnrecognisedQueueResponseFactory::class),
            $this->createStub(QueuePoppedResponseFactory::class),
            $this->createStub(PopQueueHandler::class),
            $this->createStub(PopQueueModalFactory::class),
            $this->createStub(ModalService::class),
            $this->getLogger(),
        );
    }
}

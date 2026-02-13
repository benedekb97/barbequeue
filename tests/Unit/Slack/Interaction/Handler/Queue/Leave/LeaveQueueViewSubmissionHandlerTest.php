<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Interaction\Handler\Queue\Leave;

use App\Service\Queue\Leave\LeaveQueueHandler;
use App\Slack\Interaction\Handler\Queue\Leave\LeaveQueueViewSubmissionHandler;
use App\Slack\Interaction\Handler\SlackInteractionHandlerInterface;
use App\Slack\Interaction\Interaction;
use App\Slack\Interaction\InteractionType;
use App\Slack\Interaction\SlackInteraction;
use App\Slack\Interaction\SlackViewSubmission;
use App\Slack\Response\Interaction\Factory\GenericFailureResponseFactory;
use App\Slack\Response\Interaction\Factory\Queue\Common\UnrecognisedQueueResponseFactory;
use App\Slack\Response\Interaction\Factory\Queue\Leave\QueueLeftResponseFactory;
use App\Slack\Response\Interaction\Factory\Queue\Leave\UnableToLeaveQueueResponseFactory;
use App\Slack\Surface\Factory\Modal\LeaveQueueModalFactory;
use App\Slack\Surface\Service\ModalService;
use App\Tests\Unit\Slack\Interaction\Handler\AbstractInteractionHandlerTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(LeaveQueueViewSubmissionHandler::class)]
class LeaveQueueViewSubmissionHandlerTest extends AbstractInteractionHandlerTestCase
{
    #[Test]
    public function itShouldThrowBadMethodCallExceptionIfSlackViewSubmissionPassed(): void
    {
        /** @var LeaveQueueViewSubmissionHandler $handler */
        $handler = $this->getHandler();

        $this->expectException(\BadMethodCallException::class);

        $handler->getContext($this->createStub(SlackInteraction::class));
    }

    #[Test]
    public function itShouldLogWarningIfSlackInteractionPassed(): void
    {
        $this->expectsWarning('Failed to create context for view submission');

        /** @var LeaveQueueViewSubmissionHandler $handler */
        $handler = $this->getHandler();

        $handler->handle($this->createStub(SlackInteraction::class));
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

        /** @var LeaveQueueViewSubmissionHandler $handler */
        $handler = $this->getHandler();

        $result = $handler->getContext($interaction);

        $this->assertSame($queueName, $result->getQueueIdentifier());
        $this->assertSame($teamId, $result->getTeamId());
        $this->assertSame($userId, $result->getUserId());
        $this->assertSame($queuedUserId, $result->getQueuedUserId());
    }

    protected function getSupportedInteraction(): Interaction
    {
        return Interaction::LEAVE_QUEUE;
    }

    protected function getUnsupportedInteraction(): Interaction
    {
        return Interaction::POP_QUEUE_ACTION;
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
        return new LeaveQueueViewSubmissionHandler(
            $this->createStub(UnrecognisedQueueResponseFactory::class),
            $this->createStub(UnableToLeaveQueueResponseFactory::class),
            $this->createStub(QueueLeftResponseFactory::class),
            $this->createStub(GenericFailureResponseFactory::class),
            $this->createStub(LeaveQueueHandler::class),
            $this->createStub(LeaveQueueModalFactory::class),
            $this->createStub(ModalService::class),
            $this->getLogger(),
        );
    }
}

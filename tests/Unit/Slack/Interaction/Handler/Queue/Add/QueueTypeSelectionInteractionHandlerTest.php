<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Interaction\Handler\Queue\Add;

use App\Entity\Administrator;
use App\Entity\Workspace;
use App\Enum\Queue;
use App\Slack\Common\Validator\AuthorisationValidator;
use App\Slack\Interaction\Handler\Queue\Add\QueueTypeSelectionInteractionHandler;
use App\Slack\Interaction\Handler\SlackInteractionHandlerInterface;
use App\Slack\Interaction\Interaction;
use App\Slack\Interaction\InteractionType;
use App\Slack\Interaction\SlackInteraction;
use App\Slack\Response\Interaction\Factory\Administrator\UnauthorisedResponseFactory;
use App\Slack\Surface\Component\ModalSurface;
use App\Slack\Surface\Factory\Modal\AddQueueModalFactory;
use App\Slack\Surface\Service\ModalService;
use App\Tests\Unit\Slack\Interaction\Handler\AbstractInteractionHandlerTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(QueueTypeSelectionInteractionHandler::class)]
class QueueTypeSelectionInteractionHandlerTest extends AbstractInteractionHandlerTestCase
{
    #[Test]
    public function itShouldReturnIfWorkspaceIsNull(): void
    {
        $interaction = $this->createMock(SlackInteraction::class);
        $interaction->expects($this->once())
            ->method('getAdministrator')
            ->willReturn(null);

        $interaction->expects($this->once())
            ->method('getValue')
            ->willReturn('allow-jumps');

        /** @var QueueTypeSelectionInteractionHandler $handler */
        $handler = $this->getHandler();

        $handler->run($interaction);
    }

    #[Test]
    public function itShouldReturnIfModalCreationFailed(): void
    {
        $interaction = $this->createMock(SlackInteraction::class);
        $interaction->expects($this->once())
            ->method('getAdministrator')
            ->willReturn($administrator = $this->createMock(Administrator::class));

        $interaction->expects($this->once())
            ->method('getValue')
            ->willReturn('deployment');

        $administrator->expects($this->once())
            ->method('getWorkspace')
            ->willReturn($workspace = $this->createStub(Workspace::class));

        $modalFactory = $this->createMock(AddQueueModalFactory::class);
        $modalFactory->expects($this->once())
            ->method('create')
            ->with(Queue::DEPLOYMENT, $interaction, $workspace)
            ->willReturn(null);

        $handler = new QueueTypeSelectionInteractionHandler(
            $this->createStub(AuthorisationValidator::class),
            $this->createStub(UnauthorisedResponseFactory::class),
            $modalFactory,
            $this->createStub(ModalService::class),
        );

        $handler->run($interaction);
    }

    #[Test]
    public function itShouldUpdateExistingModal(): void
    {
        $interaction = $this->createMock(SlackInteraction::class);
        $interaction->expects($this->once())
            ->method('getAdministrator')
            ->willReturn($administrator = $this->createMock(Administrator::class));

        $interaction->expects($this->once())
            ->method('getValue')
            ->willReturn('deployment');

        $interaction->expects($this->once())
            ->method('getViewId')
            ->willReturn($viewId = 'viewId');

        $administrator->expects($this->once())
            ->method('getWorkspace')
            ->willReturn($workspace = $this->createStub(Workspace::class));

        $modalFactory = $this->createMock(AddQueueModalFactory::class);
        $modalFactory->expects($this->once())
            ->method('create')
            ->with(Queue::DEPLOYMENT, $interaction, $workspace)
            ->willReturn($modal = $this->createStub(ModalSurface::class));

        $modalService = $this->createMock(ModalService::class);
        $modalService->expects($this->once())
            ->method('updateModal')
            ->with($modal, $workspace, $viewId);

        $handler = new QueueTypeSelectionInteractionHandler(
            $this->createStub(AuthorisationValidator::class),
            $this->createStub(UnauthorisedResponseFactory::class),
            $modalFactory,
            $modalService,
        );

        $handler->run($interaction);
    }

    protected function getSupportedInteraction(): Interaction
    {
        return Interaction::QUEUE_TYPE;
    }

    protected function getUnsupportedInteraction(): Interaction
    {
        return Interaction::ADD_SIMPLE_QUEUE;
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
        return new QueueTypeSelectionInteractionHandler(
            $this->createStub(AuthorisationValidator::class),
            $this->createStub(UnauthorisedResponseFactory::class),
            $this->createStub(AddQueueModalFactory::class),
            $this->createStub(ModalService::class),
        );
    }
}

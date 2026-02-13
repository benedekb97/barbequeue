<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Interaction\Handler\Repository;

use App\Entity\Administrator;
use App\Entity\Workspace;
use App\Service\Administrator\Exception\UnauthorisedException;
use App\Service\Repository\Exception\RepositoryNotFoundException;
use App\Service\Repository\RepositoryManager;
use App\Slack\Common\Validator\AuthorisationValidator;
use App\Slack\Interaction\Handler\Repository\RemoveRepositoryInteractionHandler;
use App\Slack\Interaction\Handler\SlackInteractionHandlerInterface;
use App\Slack\Interaction\Interaction;
use App\Slack\Interaction\InteractionType;
use App\Slack\Interaction\SlackInteraction;
use App\Slack\Response\Interaction\Factory\Administrator\UnauthorisedResponseFactory;
use App\Slack\Response\Interaction\Factory\GenericFailureResponseFactory;
use App\Slack\Response\Interaction\Factory\Repository\RemoveRepositoryCancelledResponseFactory;
use App\Slack\Response\Interaction\Factory\Repository\RepositoryAlreadyRemovedResponseFactory;
use App\Slack\Response\Interaction\Factory\Repository\RepositoryRemovedResponseFactory;
use App\Slack\Response\Interaction\SlackInteractionResponse;
use App\Tests\Unit\Slack\Interaction\Handler\AbstractInteractionHandlerTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(RemoveRepositoryInteractionHandler::class)]
class RemoveRepositoryInteractionHandlerTest extends AbstractInteractionHandlerTestCase
{
    #[Test]
    public function itShouldCreateRemoveRepositoryCancelledResponseIfValueIsNo(): void
    {
        $interaction = $this->createMock(SlackInteraction::class);
        $interaction->expects($this->once())
            ->method('getValue')
            ->willReturn('no');

        $responseFactory = $this->createMock(RemoveRepositoryCancelledResponseFactory::class);
        $responseFactory->expects($this->once())
            ->method('create')
            ->willReturn($response = $this->createStub(SlackInteractionResponse::class));

        $interaction->expects($this->once())
            ->method('setResponse')
            ->with($response);

        $handler = new RemoveRepositoryInteractionHandler(
            $this->createStub(AuthorisationValidator::class),
            $this->createStub(UnauthorisedResponseFactory::class),
            $responseFactory,
            $this->createStub(RepositoryManager::class),
            $this->createStub(RepositoryRemovedResponseFactory::class),
            $this->createStub(RepositoryAlreadyRemovedResponseFactory::class),
            $this->createStub(GenericFailureResponseFactory::class),
        );

        $handler->run($interaction);
    }

    #[Test]
    public function itShouldCreateUnauthorisedResponseIfUnauthorisedExceptionThrown(): void
    {
        $administrator = $this->createMock(Administrator::class);
        $administrator->expects($this->once())
            ->method('getWorkspace')
            ->willReturn($workspace = $this->createStub(Workspace::class));

        $interaction = $this->createMock(SlackInteraction::class);
        $interaction->expects($this->exactly(2))
            ->method('getValue')
            ->willReturn((string) ($id = 1));

        $interaction->expects($this->once())
            ->method('getAdministrator')
            ->willReturn($administrator);

        $manager = $this->createMock(RepositoryManager::class);
        $manager->expects($this->once())
            ->method('removeRepository')
            ->with($id, $workspace)
            ->willThrowException($this->createStub(UnauthorisedException::class));

        $removeRepositoryCancelledResponseFactory = $this->createMock(RemoveRepositoryCancelledResponseFactory::class);
        $removeRepositoryCancelledResponseFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $repositoryRemovedResponseFactory = $this->createMock(RepositoryRemovedResponseFactory::class);
        $repositoryRemovedResponseFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $unauthorisedResponseFactory = $this->createMock(UnauthorisedResponseFactory::class);
        $unauthorisedResponseFactory->expects($this->once())
            ->method('create')
            ->willReturn($response = $this->createStub(SlackInteractionResponse::class));

        $repositoryAlreadyRemovedResponseFactory = $this->createMock(RepositoryAlreadyRemovedResponseFactory::class);
        $repositoryAlreadyRemovedResponseFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $genericFailureResponseFactory = $this->createMock(GenericFailureResponseFactory::class);
        $genericFailureResponseFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $interaction->expects($this->once())
            ->method('setResponse')
            ->with($response);

        $handler = new RemoveRepositoryInteractionHandler(
            $this->createStub(AuthorisationValidator::class),
            $unauthorisedResponseFactory,
            $removeRepositoryCancelledResponseFactory,
            $manager,
            $repositoryRemovedResponseFactory,
            $repositoryAlreadyRemovedResponseFactory,
            $genericFailureResponseFactory,
        );

        $handler->run($interaction);
    }

    #[Test]
    public function itShouldRepositoryAlreadyRemovedResponseIfRepositoryAlreadyRemovedExceptionThrown(): void
    {
        $administrator = $this->createMock(Administrator::class);
        $administrator->expects($this->once())
            ->method('getWorkspace')
            ->willReturn($workspace = $this->createStub(Workspace::class));

        $interaction = $this->createMock(SlackInteraction::class);
        $interaction->expects($this->exactly(2))
            ->method('getValue')
            ->willReturn((string) ($id = 1));

        $interaction->expects($this->once())
            ->method('getAdministrator')
            ->willReturn($administrator);

        $manager = $this->createMock(RepositoryManager::class);
        $manager->expects($this->once())
            ->method('removeRepository')
            ->with($id, $workspace)
            ->willThrowException($this->createStub(RepositoryNotFoundException::class));

        $removeRepositoryCancelledResponseFactory = $this->createMock(RemoveRepositoryCancelledResponseFactory::class);
        $removeRepositoryCancelledResponseFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $repositoryRemovedResponseFactory = $this->createMock(RepositoryRemovedResponseFactory::class);
        $repositoryRemovedResponseFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $unauthorisedResponseFactory = $this->createMock(UnauthorisedResponseFactory::class);
        $unauthorisedResponseFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $repositoryAlreadyRemovedResponseFactory = $this->createMock(RepositoryAlreadyRemovedResponseFactory::class);
        $repositoryAlreadyRemovedResponseFactory->expects($this->once())
            ->method('create')
            ->willReturn($response = $this->createStub(SlackInteractionResponse::class));

        $genericFailureResponseFactory = $this->createMock(GenericFailureResponseFactory::class);
        $genericFailureResponseFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $interaction->expects($this->once())
            ->method('setResponse')
            ->with($response);

        $handler = new RemoveRepositoryInteractionHandler(
            $this->createStub(AuthorisationValidator::class),
            $unauthorisedResponseFactory,
            $removeRepositoryCancelledResponseFactory,
            $manager,
            $repositoryRemovedResponseFactory,
            $repositoryAlreadyRemovedResponseFactory,
            $genericFailureResponseFactory,
        );

        $handler->run($interaction);
    }

    #[Test]
    public function itShouldRepositoryRemovedResponse(): void
    {
        $administrator = $this->createMock(Administrator::class);
        $administrator->expects($this->once())
            ->method('getWorkspace')
            ->willReturn($workspace = $this->createStub(Workspace::class));

        $interaction = $this->createMock(SlackInteraction::class);
        $interaction->expects($this->exactly(2))
            ->method('getValue')
            ->willReturn((string) ($id = 1));

        $interaction->expects($this->once())
            ->method('getAdministrator')
            ->willReturn($administrator);

        $manager = $this->createMock(RepositoryManager::class);
        $manager->expects($this->once())
            ->method('removeRepository')
            ->with($id, $workspace)
            ->willReturn($name = 'name');

        $removeRepositoryCancelledResponseFactory = $this->createMock(RemoveRepositoryCancelledResponseFactory::class);
        $removeRepositoryCancelledResponseFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $repositoryRemovedResponseFactory = $this->createMock(RepositoryRemovedResponseFactory::class);
        $repositoryRemovedResponseFactory->expects($this->once())
            ->method('create')
            ->with($name)
            ->willReturn($response = $this->createStub(SlackInteractionResponse::class));

        $unauthorisedResponseFactory = $this->createMock(UnauthorisedResponseFactory::class);
        $unauthorisedResponseFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $repositoryAlreadyRemovedResponseFactory = $this->createMock(RepositoryAlreadyRemovedResponseFactory::class);
        $repositoryAlreadyRemovedResponseFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $genericFailureResponseFactory = $this->createMock(GenericFailureResponseFactory::class);
        $genericFailureResponseFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $interaction->expects($this->once())
            ->method('setResponse')
            ->with($response);

        $handler = new RemoveRepositoryInteractionHandler(
            $this->createStub(AuthorisationValidator::class),
            $unauthorisedResponseFactory,
            $removeRepositoryCancelledResponseFactory,
            $manager,
            $repositoryRemovedResponseFactory,
            $repositoryAlreadyRemovedResponseFactory,
            $genericFailureResponseFactory,
        );

        $handler->run($interaction);
    }

    protected function getSupportedInteraction(): Interaction
    {
        return Interaction::REMOVE_REPOSITORY;
    }

    protected function getUnsupportedInteraction(): Interaction
    {
        return Interaction::ADD_REPOSITORY;
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
        return new RemoveRepositoryInteractionHandler(
            $this->createStub(AuthorisationValidator::class),
            $this->createStub(UnauthorisedResponseFactory::class),
            $this->createStub(RemoveRepositoryCancelledResponseFactory::class),
            $this->createStub(RepositoryManager::class),
            $this->createStub(RepositoryRemovedResponseFactory::class),
            $this->createStub(RepositoryAlreadyRemovedResponseFactory::class),
            $this->createStub(GenericFailureResponseFactory::class),
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Interaction\Handler\Repository;

use App\Entity\Administrator;
use App\Entity\Repository;
use App\Entity\Workspace;
use App\Service\Administrator\Exception\UnauthorisedException;
use App\Service\Repository\Exception\RepositoryAlreadyExistsException;
use App\Service\Repository\Exception\RepositoryNotFoundException;
use App\Service\Repository\RepositoryManager;
use App\Slack\Common\Validator\AuthorisationValidator;
use App\Slack\Interaction\Handler\Repository\EditRepositoryInteractionHandler;
use App\Slack\Interaction\Handler\SlackInteractionHandlerInterface;
use App\Slack\Interaction\Interaction;
use App\Slack\Interaction\InteractionType;
use App\Slack\Interaction\SlackInteraction;
use App\Slack\Interaction\SlackViewSubmission;
use App\Slack\Response\Interaction\Factory\Administrator\UnauthorisedResponseFactory;
use App\Slack\Response\Interaction\Factory\Repository\RepositoryAlreadyExistsResponseFactory;
use App\Slack\Response\Interaction\Factory\Repository\RepositoryEditedResponseFactory;
use App\Slack\Response\Interaction\Factory\Repository\RepositoryNotFoundResponseFactory;
use App\Slack\Response\Interaction\SlackInteractionResponse;
use App\Slack\Surface\Component\ModalArgument;
use App\Tests\Unit\Slack\Interaction\Handler\AbstractInteractionHandlerTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(EditRepositoryInteractionHandler::class)]
class EditRepositoryInteractionHandlerTest extends AbstractInteractionHandlerTestCase
{
    #[Test]
    public function itShouldReturnEarlyIfNotViewSubmission(): void
    {
        $interaction = $this->createStub(SlackInteraction::class);

        $manager = $this->createMock(RepositoryManager::class);
        $manager->expects($this->never())
            ->method('editRepository')
            ->withAnyParameters();

        $handler = new EditRepositoryInteractionHandler(
            $this->createStub(AuthorisationValidator::class),
            $this->createStub(UnauthorisedResponseFactory::class),
            $manager,
            $this->createStub(RepositoryNotFoundResponseFactory::class),
            $this->createStub(RepositoryAlreadyExistsResponseFactory::class),
            $this->createStub(RepositoryEditedResponseFactory::class),
        );

        $handler->run($interaction);
    }

    #[Test]
    public function itShouldCreateUnauthorisedPrivateMessageResponseIfUnauthorisedExceptionThrown(): void
    {
        $manager = $this->createMock(RepositoryManager::class);
        $manager->expects($this->once())
            ->method('editRepository')
            ->with(
                $id = 1,
                $name = 'name',
                $url = 'url',
                $ids = [1],
                $workspace = $this->createStub(Workspace::class),
            )
            ->willThrowException($this->createStub(UnauthorisedException::class));

        $interaction = $this->createMock(SlackViewSubmission::class);
        $interaction->expects($this->once())
            ->method('getArgumentInteger')
            ->with('repository_id')
            ->willReturn($id);

        $interaction->expects($this->once())
            ->method('getArgumentIntArray')
            ->with(ModalArgument::REPOSITORY_BLOCKS->value)
            ->willReturn($ids);

        $callCount = 0;

        $interaction->expects($this->exactly(2))
            ->method('getArgumentString')
            ->willReturnCallback(function ($argument) use (&$callCount, $name, $url) {
                if (1 === ++$callCount) {
                    $this->assertEquals('repository_name', $argument);

                    return $name;
                }

                $this->assertEquals('repository_url', $argument);

                return $url;
            });

        $administrator = $this->createMock(Administrator::class);
        $administrator->expects($this->once())
            ->method('getWorkspace')
            ->willReturn($workspace);

        $interaction->expects($this->once())
            ->method('getAdministrator')
            ->willReturn($administrator);

        $repositoryEditedMessageFactory = $this->createMock(RepositoryEditedResponseFactory::class);
        $repositoryEditedMessageFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $unauthorisedResponseFactory = $this->createMock(UnauthorisedResponseFactory::class);
        $unauthorisedResponseFactory->expects($this->once())
            ->method('create')
            ->willReturn($response = $this->createStub(SlackInteractionResponse::class));

        $repositoryNotFoundMessageFactory = $this->createMock(RepositoryNotFoundResponseFactory::class);
        $repositoryNotFoundMessageFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $repositoryAlreadyExistsMessageFactory = $this->createMock(RepositoryAlreadyExistsResponseFactory::class);
        $repositoryAlreadyExistsMessageFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $interaction->expects($this->once())
            ->method('setResponse')
            ->with($response);

        $handler = new EditRepositoryInteractionHandler(
            $this->createStub(AuthorisationValidator::class),
            $unauthorisedResponseFactory,
            $manager,
            $repositoryNotFoundMessageFactory,
            $repositoryAlreadyExistsMessageFactory,
            $repositoryEditedMessageFactory,
        );

        $handler->run($interaction);
    }

    #[Test]
    public function itShouldCreateRepositoryNotFoundMessageIfRepositoryNotFoundExceptionThrown(): void
    {
        $manager = $this->createMock(RepositoryManager::class);
        $manager->expects($this->once())
            ->method('editRepository')
            ->with(
                $id = 1,
                $name = 'name',
                $url = 'url',
                $ids = [1],
                $workspace = $this->createStub(Workspace::class),
            )
            ->willThrowException($this->createStub(RepositoryNotFoundException::class));

        $interaction = $this->createMock(SlackViewSubmission::class);
        $interaction->expects($this->once())
            ->method('getArgumentInteger')
            ->with('repository_id')
            ->willReturn($id);

        $interaction->expects($this->once())
            ->method('getArgumentIntArray')
            ->with(ModalArgument::REPOSITORY_BLOCKS->value)
            ->willReturn($ids);

        $callCount = 0;

        $interaction->expects($this->exactly(2))
            ->method('getArgumentString')
            ->willReturnCallback(function ($argument) use (&$callCount, $name, $url) {
                if (1 === ++$callCount) {
                    $this->assertEquals('repository_name', $argument);

                    return $name;
                }

                $this->assertEquals('repository_url', $argument);

                return $url;
            });

        $administrator = $this->createMock(Administrator::class);
        $administrator->expects($this->once())
            ->method('getWorkspace')
            ->willReturn($workspace);

        $interaction->expects($this->once())
            ->method('getAdministrator')
            ->willReturn($administrator);

        $repositoryEditedMessageFactory = $this->createMock(RepositoryEditedResponseFactory::class);
        $repositoryEditedMessageFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $repositoryNotFoundMessageFactory = $this->createMock(RepositoryNotFoundResponseFactory::class);
        $repositoryNotFoundMessageFactory->expects($this->once())
            ->method('create')
            ->willReturn($response = $this->createStub(SlackInteractionResponse::class));

        $repositoryAlreadyExistsMessageFactory = $this->createMock(RepositoryAlreadyExistsResponseFactory::class);
        $repositoryAlreadyExistsMessageFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $interaction->expects($this->once())
            ->method('setResponse')
            ->with($response);

        $handler = new EditRepositoryInteractionHandler(
            $this->createStub(AuthorisationValidator::class),
            $this->createStub(UnauthorisedResponseFactory::class),
            $manager,
            $repositoryNotFoundMessageFactory,
            $repositoryAlreadyExistsMessageFactory,
            $repositoryEditedMessageFactory,
        );

        $handler->run($interaction);
    }

    #[Test]
    public function itShouldCreateRepositoryAlreadyExistsMessageIfRepositoryAlreadyExistsExceptionThrown(): void
    {
        $exception = $this->createMock(RepositoryAlreadyExistsException::class);
        $exception->expects($this->once())
            ->method('getName')
            ->willReturn($name = 'name');

        $manager = $this->createMock(RepositoryManager::class);
        $manager->expects($this->once())
            ->method('editRepository')
            ->with(
                $id = 1,
                $name,
                $url = 'url',
                $ids = [1],
                $workspace = $this->createStub(Workspace::class),
            )
            ->willThrowException($exception);

        $interaction = $this->createMock(SlackViewSubmission::class);
        $interaction->expects($this->once())
            ->method('getArgumentInteger')
            ->with('repository_id')
            ->willReturn($id);

        $interaction->expects($this->once())
            ->method('getArgumentIntArray')
            ->with(ModalArgument::REPOSITORY_BLOCKS->value)
            ->willReturn($ids);

        $callCount = 0;

        $interaction->expects($this->exactly(2))
            ->method('getArgumentString')
            ->willReturnCallback(function ($argument) use (&$callCount, $name, $url) {
                if (1 === ++$callCount) {
                    $this->assertEquals('repository_name', $argument);

                    return $name;
                }

                $this->assertEquals('repository_url', $argument);

                return $url;
            });

        $administrator = $this->createMock(Administrator::class);
        $administrator->expects($this->once())
            ->method('getWorkspace')
            ->willReturn($workspace);

        $interaction->expects($this->once())
            ->method('getAdministrator')
            ->willReturn($administrator);

        $repositoryEditedMessageFactory = $this->createMock(RepositoryEditedResponseFactory::class);
        $repositoryEditedMessageFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $repositoryNotFoundMessageFactory = $this->createMock(RepositoryNotFoundResponseFactory::class);
        $repositoryNotFoundMessageFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $repositoryAlreadyExistsMessageFactory = $this->createMock(RepositoryAlreadyExistsResponseFactory::class);
        $repositoryAlreadyExistsMessageFactory->expects($this->once())
            ->method('create')
            ->with($name)
            ->willReturn($response = $this->createStub(SlackInteractionResponse::class));

        $interaction->expects($this->once())
            ->method('setResponse')
            ->with($response);

        $handler = new EditRepositoryInteractionHandler(
            $this->createStub(AuthorisationValidator::class),
            $this->createStub(UnauthorisedResponseFactory::class),
            $manager,
            $repositoryNotFoundMessageFactory,
            $repositoryAlreadyExistsMessageFactory,
            $repositoryEditedMessageFactory,
        );

        $handler->run($interaction);
    }

    #[Test]
    public function itShouldCreateRepositoryEditedMessage(): void
    {
        $manager = $this->createMock(RepositoryManager::class);
        $manager->expects($this->once())
            ->method('editRepository')
            ->with(
                $id = 1,
                $name = 'name',
                $url = 'url',
                $ids = [1],
                $workspace = $this->createStub(Workspace::class),
            )
            ->willReturn($repository = $this->createStub(Repository::class));

        $interaction = $this->createMock(SlackViewSubmission::class);
        $interaction->expects($this->once())
            ->method('getArgumentInteger')
            ->with('repository_id')
            ->willReturn($id);

        $interaction->expects($this->once())
            ->method('getArgumentIntArray')
            ->with(ModalArgument::REPOSITORY_BLOCKS->value)
            ->willReturn($ids);

        $callCount = 0;

        $interaction->expects($this->exactly(2))
            ->method('getArgumentString')
            ->willReturnCallback(function ($argument) use (&$callCount, $name, $url) {
                if (1 === ++$callCount) {
                    $this->assertEquals('repository_name', $argument);

                    return $name;
                }

                $this->assertEquals('repository_url', $argument);

                return $url;
            });

        $administrator = $this->createMock(Administrator::class);
        $administrator->expects($this->once())
            ->method('getWorkspace')
            ->willReturn($workspace);

        $interaction->expects($this->once())
            ->method('getAdministrator')
            ->willReturn($administrator);

        $repositoryEditedMessageFactory = $this->createMock(RepositoryEditedResponseFactory::class);
        $repositoryEditedMessageFactory->expects($this->once())
            ->method('create')
            ->with($repository)
            ->willReturn($response = $this->createStub(SlackInteractionResponse::class));

        $repositoryNotFoundMessageFactory = $this->createMock(RepositoryNotFoundResponseFactory::class);
        $repositoryNotFoundMessageFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $repositoryAlreadyExistsMessageFactory = $this->createMock(RepositoryAlreadyExistsResponseFactory::class);
        $repositoryAlreadyExistsMessageFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $interaction->expects($this->once())
            ->method('setResponse')
            ->with($response);

        $handler = new EditRepositoryInteractionHandler(
            $this->createStub(AuthorisationValidator::class),
            $this->createStub(UnauthorisedResponseFactory::class),
            $manager,
            $repositoryNotFoundMessageFactory,
            $repositoryAlreadyExistsMessageFactory,
            $repositoryEditedMessageFactory,
        );

        $handler->run($interaction);
    }

    protected function getSupportedInteraction(): Interaction
    {
        return Interaction::EDIT_REPOSITORY;
    }

    protected function getUnsupportedInteraction(): Interaction
    {
        return Interaction::ADD_REPOSITORY;
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
        return new EditRepositoryInteractionHandler(
            $this->createStub(AuthorisationValidator::class),
            $this->createStub(UnauthorisedResponseFactory::class),
            $this->createStub(RepositoryManager::class),
            $this->createStub(RepositoryNotFoundResponseFactory::class),
            $this->createStub(RepositoryAlreadyExistsResponseFactory::class),
            $this->createStub(RepositoryEditedResponseFactory::class),
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Interaction\Handler\Repository;

use App\Entity\Administrator;
use App\Entity\Repository;
use App\Service\Administrator\Exception\UnauthorisedException;
use App\Service\Repository\Exception\RepositoryAlreadyExistsException;
use App\Service\Repository\RepositoryManager;
use App\Slack\Common\Validator\AuthorisationValidator;
use App\Slack\Interaction\Handler\Repository\AddRepositoryInteractionHandler;
use App\Slack\Interaction\Handler\SlackInteractionHandlerInterface;
use App\Slack\Interaction\Interaction;
use App\Slack\Interaction\InteractionType;
use App\Slack\Interaction\SlackInteraction;
use App\Slack\Interaction\SlackViewSubmission;
use App\Slack\Response\Interaction\Factory\Administrator\UnauthorisedResponseFactory;
use App\Slack\Response\Interaction\Factory\Repository\RepositoryAddedResponseFactory;
use App\Slack\Response\Interaction\Factory\Repository\RepositoryAlreadyExistsResponseFactory;
use App\Slack\Response\Interaction\SlackInteractionResponse;
use App\Slack\Surface\Component\ModalArgument;
use App\Tests\Unit\Slack\Interaction\Handler\AbstractInteractionHandlerTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(AddRepositoryInteractionHandler::class)]
class AddRepositoryInteractionHandlerTest extends AbstractInteractionHandlerTestCase
{
    #[Test]
    public function itShouldReturnIfInteractionNotViewSubmission(): void
    {
        $manager = $this->createMock(RepositoryManager::class);
        $manager->expects($this->never())
            ->method($this->anything())
            ->withAnyParameters();

        $handler = new AddRepositoryInteractionHandler(
            $this->createStub(AuthorisationValidator::class),
            $this->createStub(UnauthorisedResponseFactory::class),
            $manager,
            $this->createStub(RepositoryAddedResponseFactory::class),
            $this->createStub(RepositoryAlreadyExistsResponseFactory::class),
        );

        $handler->run($this->createStub(SlackInteraction::class));
    }

    #[Test]
    public function itShouldCreateUnauthorisedPrivateMessageResponseIfManagerThrowsUnauthorisedException(): void
    {
        $name = 'name';
        $url = 'url';

        $administrator = $this->createMock(Administrator::class);
        $administrator->expects($this->once())
            ->method('getWorkspace')
            ->willReturn($workspace = null);

        $interaction = $this->createMock(SlackViewSubmission::class);
        $interaction->expects($this->once())
            ->method('getArgumentIntArray')
            ->with(ModalArgument::REPOSITORY_BLOCKS->value)
            ->willReturn($ids = [1]);

        $callCount = 0;

        $interaction->expects($this->exactly(2))
            ->method('getArgumentString')
            ->willReturnCallback(function ($argument) use (&$callCount, $name, $url) {
                if (1 === ++$callCount) {
                    $this->assertEquals(ModalArgument::REPOSITORY_NAME->value, $argument);

                    return $name;
                }

                $this->assertEquals(ModalArgument::REPOSITORY_URL->value, $argument);

                return $url;
            });

        $interaction->expects($this->once())
            ->method('getAdministrator')
            ->willReturn($administrator);

        $unauthorisedResponseFactory = $this->createMock(UnauthorisedResponseFactory::class);
        $unauthorisedResponseFactory->expects($this->once())
            ->method('create')
            ->willReturn($response = $this->createStub(SlackInteractionResponse::class));

        $repositoryAddedMessageFactory = $this->createMock(RepositoryAddedResponseFactory::class);
        $repositoryAddedMessageFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $repositoryAlreadyExistsMessageFactory = $this->createMock(RepositoryAlreadyExistsResponseFactory::class);
        $repositoryAlreadyExistsMessageFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $exception = $this->createStub(UnauthorisedException::class);

        $manager = $this->createMock(RepositoryManager::class);
        $manager->expects($this->once())
            ->method('addRepository')
            ->with($name, $url, $ids, $workspace)
            ->wilLThrowException($exception);

        $interaction->expects($this->once())
            ->method('setResponse')
            ->with($response);

        $handler = new AddRepositoryInteractionHandler(
            $this->createStub(AuthorisationValidator::class),
            $unauthorisedResponseFactory,
            $manager,
            $repositoryAddedMessageFactory,
            $repositoryAlreadyExistsMessageFactory,
        );

        $handler->run($interaction);
    }

    #[Test]
    public function itShouldCreateRepositoryAlreadyExistsMessageIfRepositoryAlreadyExistsExceptionThrown(): void
    {
        $name = 'name';
        $url = 'url';

        $administrator = $this->createMock(Administrator::class);
        $administrator->expects($this->once())
            ->method('getWorkspace')
            ->willReturn($workspace = null);

        $interaction = $this->createMock(SlackViewSubmission::class);
        $interaction->expects($this->once())
            ->method('getArgumentIntArray')
            ->with(ModalArgument::REPOSITORY_BLOCKS->value)
            ->willReturn($ids = [1]);

        $callCount = 0;

        $interaction->expects($this->exactly(2))
            ->method('getArgumentString')
            ->willReturnCallback(function ($argument) use (&$callCount, $name, $url) {
                if (1 === ++$callCount) {
                    $this->assertEquals(ModalArgument::REPOSITORY_NAME->value, $argument);

                    return $name;
                }

                $this->assertEquals(ModalArgument::REPOSITORY_URL->value, $argument);

                return $url;
            });

        $interaction->expects($this->once())
            ->method('getAdministrator')
            ->willReturn($administrator);

        $exception = $this->createMock(RepositoryAlreadyExistsException::class);
        $exception->expects($this->once())
            ->method('getName')
            ->willReturn($name);

        $repositoryAddedMessageFactory = $this->createMock(RepositoryAddedResponseFactory::class);
        $repositoryAddedMessageFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $repositoryAlreadyExistsMessageFactory = $this->createMock(RepositoryAlreadyExistsResponseFactory::class);
        $repositoryAlreadyExistsMessageFactory->expects($this->once())
            ->method('create')
            ->with($name)
            ->willReturn($response = $this->createStub(SlackInteractionResponse::class));

        $manager = $this->createMock(RepositoryManager::class);
        $manager->expects($this->once())
            ->method('addRepository')
            ->with($name, $url, $ids, $workspace)
            ->wilLThrowException($exception);

        $interaction->expects($this->once())
            ->method('setResponse')
            ->with($response);

        $handler = new AddRepositoryInteractionHandler(
            $this->createStub(AuthorisationValidator::class),
            $this->createStub(UnauthorisedResponseFactory::class),
            $manager,
            $repositoryAddedMessageFactory,
            $repositoryAlreadyExistsMessageFactory,
        );

        $handler->run($interaction);
    }

    #[Test]
    public function itShouldCreateRepositoryAddedMessage(): void
    {
        $name = 'name';
        $url = 'url';

        $administrator = $this->createMock(Administrator::class);
        $administrator->expects($this->once())
            ->method('getWorkspace')
            ->willReturn($workspace = null);

        $interaction = $this->createMock(SlackViewSubmission::class);
        $interaction->expects($this->once())
            ->method('getArgumentIntArray')
            ->with(ModalArgument::REPOSITORY_BLOCKS->value)
            ->willReturn($ids = [1]);

        $callCount = 0;

        $interaction->expects($this->exactly(2))
            ->method('getArgumentString')
            ->willReturnCallback(function ($argument) use (&$callCount, $name, $url) {
                if (1 === ++$callCount) {
                    $this->assertEquals(ModalArgument::REPOSITORY_NAME->value, $argument);

                    return $name;
                }

                $this->assertEquals(ModalArgument::REPOSITORY_URL->value, $argument);

                return $url;
            });

        $interaction->expects($this->once())
            ->method('getAdministrator')
            ->willReturn($administrator);

        $manager = $this->createMock(RepositoryManager::class);
        $manager->expects($this->once())
            ->method('addRepository')
            ->with($name, $url, $ids, $workspace)
            ->willReturn($repository = $this->createStub(Repository::class));

        $repositoryAddedMessageFactory = $this->createMock(RepositoryAddedResponseFactory::class);
        $repositoryAddedMessageFactory->expects($this->once())
            ->method('create')
            ->with($repository)
            ->willReturn($response = $this->createStub(SlackInteractionResponse::class));

        $repositoryAlreadyExistsMessageFactory = $this->createMock(RepositoryAlreadyExistsResponseFactory::class);
        $repositoryAlreadyExistsMessageFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $interaction->expects($this->once())
            ->method('setResponse')
            ->with($response);

        $handler = new AddRepositoryInteractionHandler(
            $this->createStub(AuthorisationValidator::class),
            $this->createStub(UnauthorisedResponseFactory::class),
            $manager,
            $repositoryAddedMessageFactory,
            $repositoryAlreadyExistsMessageFactory,
        );

        $handler->run($interaction);
    }

    protected function getSupportedInteraction(): Interaction
    {
        return Interaction::ADD_REPOSITORY;
    }

    protected function getUnsupportedInteraction(): Interaction
    {
        return Interaction::EDIT_QUEUE;
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
        return new AddRepositoryInteractionHandler(
            $this->createStub(AuthorisationValidator::class),
            $this->createStub(UnauthorisedResponseFactory::class),
            $this->createStub(RepositoryManager::class),
            $this->createStub(RepositoryAddedResponseFactory::class),
            $this->createStub(RepositoryAlreadyExistsResponseFactory::class),
        );
    }
}

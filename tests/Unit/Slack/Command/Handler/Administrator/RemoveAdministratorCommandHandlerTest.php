<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Command\Handler\Administrator;

use App\Entity\Administrator;
use App\Service\Administrator\AdministratorManager;
use App\Service\Administrator\Exception\AdministratorNotFoundException;
use App\Service\Administrator\Exception\UnauthorisedException;
use App\Slack\Command\Command;
use App\Slack\Command\CommandArgument;
use App\Slack\Command\Handler\Administrator\RemoveAdministratorCommandHandler;
use App\Slack\Command\Handler\SlackCommandHandlerInterface;
use App\Slack\Command\SlackCommand;
use App\Slack\Command\SubCommand;
use App\Slack\Common\Validator\AuthorisationValidator;
use App\Slack\Response\Interaction\Factory\Administrator\AdministratorNotFoundResponseFactory;
use App\Slack\Response\Interaction\Factory\Administrator\AdministratorRemovedResponseFactory;
use App\Slack\Response\Interaction\Factory\Administrator\UnauthorisedResponseFactory;
use App\Slack\Response\Interaction\Factory\GenericFailureResponseFactory;
use App\Slack\Response\Interaction\SlackInteractionResponse;
use App\Tests\Unit\Slack\Command\Handler\AbstractCommandHandlerTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(RemoveAdministratorCommandHandler::class)]
class RemoveAdministratorCommandHandlerTest extends AbstractCommandHandlerTestCase
{
    #[Test]
    public function itShouldCreateUnauthorisedResponseIfUnauthorisedExceptionThrown(): void
    {
        $command = $this->createMock(SlackCommand::class);
        $command->expects($this->once())
            ->method('getUserId')
            ->willReturn($userId = 'userId');

        $command->expects($this->exactly(2))
            ->method('getTeamId')
            ->willReturn($teamId = 'teamId');

        $command->expects($this->once())
            ->method('getCommand')
            ->willReturn($commandType = Command::BBQ_ADMIN);

        $validator = $this->createMock(AuthorisationValidator::class);
        $validator->expects($this->once())
            ->method('validate')
            ->with($commandType, $userId, $teamId)
            ->willReturn($removedBy = $this->createStub(Administrator::class));

        $command->expects($this->once())
            ->method('setAdministrator')
            ->with($removedBy);

        $command->expects($this->once())
            ->method('getArgumentString')
            ->with(CommandArgument::USER)
            ->willReturn($administratorUserId = 'administratorUserId');

        $command->expects($this->once())
            ->method('getAdministrator')
            ->willReturn($removedBy);

        $exception = $this->createStub(UnauthorisedException::class);

        $response = $this->createStub(SlackInteractionResponse::class);

        $manager = $this->createMock(AdministratorManager::class);
        $manager->expects($this->once())
            ->method('removeUser')
            ->with($administratorUserId, $teamId, $removedBy)
            ->willThrowException($exception);

        $administratorRemovedResponseFactory = $this->createMock(AdministratorRemovedResponseFactory::class);
        $administratorRemovedResponseFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $unauthorisedResponseFactory = $this->createMock(UnauthorisedResponseFactory::class);
        $unauthorisedResponseFactory->expects($this->once())
            ->method('create')
            ->with()
            ->willReturn($response);

        $administratorNotFoundResponseFactory = $this->createMock(AdministratorNotFoundResponseFactory::class);
        $administratorNotFoundResponseFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $genericResponseFactory = $this->createMock(GenericFailureResponseFactory::class);
        $genericResponseFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $command->expects($this->once())
            ->method('setResponse')
            ->with($response);

        $handler = new RemoveAdministratorCommandHandler(
            $validator,
            $unauthorisedResponseFactory,
            $manager,
            $administratorRemovedResponseFactory,
            $administratorNotFoundResponseFactory,
            $genericResponseFactory,
        );

        $handler->handle($command);
    }

    #[Test]
    public function itShouldCreateAdministratorNotFoundResponseIfAdministratorNotFoundExceptionThrown(): void
    {
        $command = $this->createMock(SlackCommand::class);
        $command->expects($this->once())
            ->method('getUserId')
            ->willReturn($userId = 'userId');

        $command->expects($this->exactly(2))
            ->method('getTeamId')
            ->willReturn($teamId = 'teamId');

        $command->expects($this->once())
            ->method('getCommand')
            ->willReturn($commandType = Command::BBQ_ADMIN);

        $validator = $this->createMock(AuthorisationValidator::class);
        $validator->expects($this->once())
            ->method('validate')
            ->with($commandType, $userId, $teamId)
            ->willReturn($removedBy = $this->createStub(Administrator::class));

        $command->expects($this->once())
            ->method('setAdministrator')
            ->with($removedBy);

        $command->expects($this->once())
            ->method('getArgumentString')
            ->with(CommandArgument::USER)
            ->willReturn($administratorUserId = 'administratorUserId');

        $command->expects($this->once())
            ->method('getAdministrator')
            ->willReturn($removedBy);

        $exception = $this->createMock(AdministratorNotFoundException::class);
        $exception->expects($this->once())
            ->method('getUserId')
            ->willReturn($administratorUserId);

        $response = $this->createStub(SlackInteractionResponse::class);

        $manager = $this->createMock(AdministratorManager::class);
        $manager->expects($this->once())
            ->method('removeUser')
            ->with($administratorUserId, $teamId, $removedBy)
            ->willThrowException($exception);

        $administratorRemovedResponseFactory = $this->createMock(AdministratorRemovedResponseFactory::class);
        $administratorRemovedResponseFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $unauthorisedResponseFactory = $this->createMock(UnauthorisedResponseFactory::class);
        $unauthorisedResponseFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $administratorNotFoundResponseFactory = $this->createMock(AdministratorNotFoundResponseFactory::class);
        $administratorNotFoundResponseFactory->expects($this->once())
            ->method('create')
            ->with($administratorUserId)
            ->willReturn($response);

        $genericResponseFactory = $this->createMock(GenericFailureResponseFactory::class);
        $genericResponseFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $command->expects($this->once())
            ->method('setResponse')
            ->with($response);

        $handler = new RemoveAdministratorCommandHandler(
            $validator,
            $unauthorisedResponseFactory,
            $manager,
            $administratorRemovedResponseFactory,
            $administratorNotFoundResponseFactory,
            $genericResponseFactory,
        );

        $handler->handle($command);
    }

    #[Test]
    public function itShouldCreateAdministratorRemovedResponse(): void
    {
        $command = $this->createMock(SlackCommand::class);
        $command->expects($this->once())
            ->method('getUserId')
            ->willReturn($userId = 'userId');

        $command->expects($this->exactly(2))
            ->method('getTeamId')
            ->willReturn($teamId = 'teamId');

        $command->expects($this->once())
            ->method('getCommand')
            ->willReturn($commandType = Command::BBQ_ADMIN);

        $validator = $this->createMock(AuthorisationValidator::class);
        $validator->expects($this->once())
            ->method('validate')
            ->with($commandType, $userId, $teamId)
            ->willReturn($removedBy = $this->createStub(Administrator::class));

        $command->expects($this->once())
            ->method('setAdministrator')
            ->with($removedBy);

        $command->expects($this->once())
            ->method('getArgumentString')
            ->with(CommandArgument::USER)
            ->willReturn($administratorUserId = 'administratorUserId');

        $command->expects($this->once())
            ->method('getAdministrator')
            ->willReturn($removedBy);

        $response = $this->createStub(SlackInteractionResponse::class);

        $manager = $this->createMock(AdministratorManager::class);
        $manager->expects($this->once())
            ->method('removeUser')
            ->with($administratorUserId, $teamId, $removedBy);

        $administratorRemovedResponseFactory = $this->createMock(AdministratorRemovedResponseFactory::class);
        $administratorRemovedResponseFactory->expects($this->once())
            ->method('create')
            ->with($administratorUserId)
            ->willReturn($response);

        $unauthorisedResponseFactory = $this->createMock(UnauthorisedResponseFactory::class);
        $unauthorisedResponseFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $administratorNotFoundResponseFactory = $this->createMock(AdministratorNotFoundResponseFactory::class);
        $administratorNotFoundResponseFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $genericResponseFactory = $this->createMock(GenericFailureResponseFactory::class);
        $genericResponseFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $command->expects($this->once())
            ->method('setResponse')
            ->with($response);

        $handler = new RemoveAdministratorCommandHandler(
            $validator,
            $unauthorisedResponseFactory,
            $manager,
            $administratorRemovedResponseFactory,
            $administratorNotFoundResponseFactory,
            $genericResponseFactory,
        );

        $handler->handle($command);
    }

    protected function getSupportedCommand(): Command
    {
        return Command::BBQ_ADMIN;
    }

    protected function getSupportedSubCommand(): SubCommand
    {
        return SubCommand::REMOVE_USER;
    }

    protected function getUnsupportedCommand(): Command
    {
        return Command::BBQ;
    }

    protected function getUnsupportedSubCommand(): SubCommand
    {
        return SubCommand::ADD_USER;
    }

    protected function getHandler(): SlackCommandHandlerInterface
    {
        return new RemoveAdministratorCommandHandler(
            $this->createStub(AuthorisationValidator::class),
            $this->createStub(UnauthorisedResponseFactory::class),
            $this->createStub(AdministratorManager::class),
            $this->createStub(AdministratorRemovedResponseFactory::class),
            $this->createStub(AdministratorNotFoundResponseFactory::class),
            $this->createStub(GenericFailureResponseFactory::class),
        );
    }
}

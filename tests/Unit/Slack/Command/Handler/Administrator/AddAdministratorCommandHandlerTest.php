<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Command\Handler\Administrator;

use App\Entity\Administrator;
use App\Service\Administrator\AdministratorManager;
use App\Service\Administrator\Exception\AdministratorExistsException;
use App\Service\Administrator\Exception\UnauthorisedException;
use App\Slack\Command\Command;
use App\Slack\Command\CommandArgument;
use App\Slack\Command\Handler\Administrator\AddAdministratorCommandHandler;
use App\Slack\Command\Handler\SlackCommandHandlerInterface;
use App\Slack\Command\SlackCommand;
use App\Slack\Command\SubCommand;
use App\Slack\Common\Validator\AuthorisationValidator;
use App\Slack\Response\Interaction\Factory\Administrator\AdministratorAddedResponseFactory;
use App\Slack\Response\Interaction\Factory\Administrator\AdministratorAlreadyExistsResponseFactory;
use App\Slack\Response\Interaction\Factory\Administrator\UnauthorisedResponseFactory;
use App\Slack\Response\Interaction\Factory\GenericFailureResponseFactory;
use App\Slack\Response\Interaction\SlackInteractionResponse;
use App\Tests\Unit\Slack\Command\Handler\AbstractCommandHandlerTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(AddAdministratorCommandHandler::class)]
class AddAdministratorCommandHandlerTest extends AbstractCommandHandlerTestCase
{
    #[Test]
    public function itShouldCreateUnauthorisedResponseIfUnauthorisedExceptionThrown(): void
    {
        $response = $this->createStub(SlackInteractionResponse::class);

        $command = $this->createMock(SlackCommand::class);
        $command->expects($this->once())
            ->method('getArgumentString')
            ->with(CommandArgument::USER)
            ->willReturn($userId = 'userId');

        $command->expects($this->exactly(2))
            ->method('getTeamId')
            ->willReturn($teamId = 'teamId');

        $command->expects($this->once())
            ->method('getAdministrator')
            ->willReturn($administrator = $this->createStub(Administrator::class));

        $command->expects($this->once())
            ->method('setResponse')
            ->with($response);

        $exception = $this->createStub(UnauthorisedException::class);

        $administratorManager = $this->createMock(AdministratorManager::class);
        $administratorManager->expects($this->once())
            ->method('addUser')
            ->with($userId, $teamId, $administrator)
            ->willThrowException($exception);

        $unauthorisedResponseFactory = $this->createMock(UnauthorisedResponseFactory::class);
        $unauthorisedResponseFactory->expects($this->once())
            ->method('create')
            ->willReturn($response);

        $administratorAlreadyExistsResponseFactory = $this->createMock(AdministratorAlreadyExistsResponseFactory::class);
        $administratorAlreadyExistsResponseFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $genericFailureResponseFactory = $this->createMock(GenericFailureResponseFactory::class);
        $genericFailureResponseFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $administratorAddedResponseFactory = $this->createMock(AdministratorAddedResponseFactory::class);
        $administratorAddedResponseFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $command->expects($this->once())
            ->method('getUserId')
            ->willReturn($userId);

        $command->expects($this->once())
            ->method('getCommand')
            ->willReturn($commandType = Command::BBQ_ADMIN);

        $validator = $this->createMock(AuthorisationValidator::class);
        $validator->expects($this->once())
            ->method('validate')
            ->with($commandType, $userId, $teamId)
            ->willReturn($administrator);

        $command->expects($this->once())
            ->method('setAdministrator')
            ->with($administrator);

        $handler = new AddAdministratorCommandHandler(
            $administratorManager,
            $administratorAddedResponseFactory,
            $administratorAlreadyExistsResponseFactory,
            $genericFailureResponseFactory,
            $validator,
            $unauthorisedResponseFactory,
        );

        $handler->handle($command);
    }

    #[Test]
    public function itShouldCreateAdministratorAlreadyExistsResponseIfAdministratorAlreadyExistsExceptionThrown(): void
    {
        $response = $this->createStub(SlackInteractionResponse::class);

        $command = $this->createMock(SlackCommand::class);
        $command->expects($this->once())
            ->method('getArgumentString')
            ->with(CommandArgument::USER)
            ->willReturn($userId = 'userId');

        $command->expects($this->exactly(2))
            ->method('getTeamId')
            ->willReturn($teamId = 'teamId');

        $command->expects($this->once())
            ->method('getAdministrator')
            ->willReturn($addedBy = $this->createStub(Administrator::class));

        $command->expects($this->once())
            ->method('setResponse')
            ->with($response);

        $exception = $this->createMock(AdministratorExistsException::class);
        $exception->expects($this->once())
            ->method('getAdministrator')
            ->willReturn($administrator = $this->createStub(Administrator::class));

        $administratorManager = $this->createMock(AdministratorManager::class);
        $administratorManager->expects($this->once())
            ->method('addUser')
            ->with($userId, $teamId, $addedBy)
            ->willThrowException($exception);

        $unauthorisedResponseFactory = $this->createMock(UnauthorisedResponseFactory::class);
        $unauthorisedResponseFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $administratorAlreadyExistsResponseFactory = $this->createMock(AdministratorAlreadyExistsResponseFactory::class);
        $administratorAlreadyExistsResponseFactory->expects($this->once())
            ->method('create')
            ->with($administrator)
            ->willReturn($response);

        $genericFailureResponseFactory = $this->createMock(GenericFailureResponseFactory::class);
        $genericFailureResponseFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $administratorAddedResponseFactory = $this->createMock(AdministratorAddedResponseFactory::class);
        $administratorAddedResponseFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $command->expects($this->once())
            ->method('getUserId')
            ->willReturn($userId);

        $command->expects($this->once())
            ->method('getCommand')
            ->willReturn($commandType = Command::BBQ_ADMIN);

        $validator = $this->createMock(AuthorisationValidator::class);
        $validator->expects($this->once())
            ->method('validate')
            ->with($commandType, $userId, $teamId)
            ->willReturn($administrator);

        $command->expects($this->once())
            ->method('setAdministrator')
            ->with($administrator);

        $handler = new AddAdministratorCommandHandler(
            $administratorManager,
            $administratorAddedResponseFactory,
            $administratorAlreadyExistsResponseFactory,
            $genericFailureResponseFactory,
            $validator,
            $unauthorisedResponseFactory,
        );

        $handler->handle($command);
    }

    #[Test]
    public function itShouldCreateAdministratorAddedResponse(): void
    {
        $response = $this->createStub(SlackInteractionResponse::class);

        $command = $this->createMock(SlackCommand::class);
        $command->expects($this->once())
            ->method('getArgumentString')
            ->with(CommandArgument::USER)
            ->willReturn($userId = 'userId');

        $command->expects($this->exactly(2))
            ->method('getTeamId')
            ->willReturn($teamId = 'teamId');

        $command->expects($this->once())
            ->method('getAdministrator')
            ->willReturn($addedBy = $this->createStub(Administrator::class));

        $command->expects($this->once())
            ->method('setResponse')
            ->with($response);

        $administratorManager = $this->createMock(AdministratorManager::class);
        $administratorManager->expects($this->once())
            ->method('addUser')
            ->with($userId, $teamId, $addedBy)
            ->willReturn($administrator = $this->createStub(Administrator::class));

        $unauthorisedResponseFactory = $this->createMock(UnauthorisedResponseFactory::class);
        $unauthorisedResponseFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $administratorAlreadyExistsResponseFactory = $this->createMock(AdministratorAlreadyExistsResponseFactory::class);
        $administratorAlreadyExistsResponseFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $genericFailureResponseFactory = $this->createMock(GenericFailureResponseFactory::class);
        $genericFailureResponseFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $administratorAddedResponseFactory = $this->createMock(AdministratorAddedResponseFactory::class);
        $administratorAddedResponseFactory->expects($this->once())
            ->method('create')
            ->with($administrator)
            ->willReturn($response);

        $command->expects($this->once())
            ->method('getUserId')
            ->willReturn($userId);

        $command->expects($this->once())
            ->method('getCommand')
            ->willReturn($commandType = Command::BBQ_ADMIN);

        $validator = $this->createMock(AuthorisationValidator::class);
        $validator->expects($this->once())
            ->method('validate')
            ->with($commandType, $userId, $teamId)
            ->willReturn($administrator);

        $command->expects($this->once())
            ->method('setAdministrator')
            ->with($administrator);

        $handler = new AddAdministratorCommandHandler(
            $administratorManager,
            $administratorAddedResponseFactory,
            $administratorAlreadyExistsResponseFactory,
            $genericFailureResponseFactory,
            $validator,
            $unauthorisedResponseFactory,
        );

        $handler->handle($command);
    }

    protected function getSupportedCommand(): Command
    {
        return Command::BBQ_ADMIN;
    }

    protected function getSupportedSubCommand(): SubCommand
    {
        return SubCommand::ADD_USER;
    }

    protected function getUnsupportedCommand(): Command
    {
        return Command::BBQ;
    }

    protected function getUnsupportedSubCommand(): SubCommand
    {
        return SubCommand::REMOVE_USER;
    }

    protected function getHandler(): SlackCommandHandlerInterface
    {
        return new AddAdministratorCommandHandler(
            $this->createStub(AdministratorManager::class),
            $this->createStub(AdministratorAddedResponseFactory::class),
            $this->createStub(AdministratorAlreadyExistsResponseFactory::class),
            $this->createStub(GenericFailureResponseFactory::class),
            $this->createStub(AuthorisationValidator::class),
            $this->createStub(UnauthorisedResponseFactory::class),
        );
    }
}

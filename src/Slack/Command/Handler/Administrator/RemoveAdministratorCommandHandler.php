<?php

declare(strict_types=1);

namespace App\Slack\Command\Handler\Administrator;

use App\Service\Administrator\AdministratorManager;
use App\Service\Administrator\Exception\AdministratorNotFoundException;
use App\Service\Administrator\Exception\UnauthorisedException;
use App\Slack\Command\Command;
use App\Slack\Command\CommandArgument;
use App\Slack\Command\Handler\AbstractAuthorisedCommandHandler;
use App\Slack\Command\Handler\SlackCommandHandlerInterface;
use App\Slack\Command\SlackCommand;
use App\Slack\Command\SubCommand;
use App\Slack\Common\Validator\AuthorisationValidator;
use App\Slack\Response\Interaction\Factory\Administrator\AdministratorNotFoundResponseFactory;
use App\Slack\Response\Interaction\Factory\Administrator\AdministratorRemovedResponseFactory;
use App\Slack\Response\Interaction\Factory\Administrator\UnauthorisedResponseFactory;
use App\Slack\Response\Interaction\Factory\GenericFailureResponseFactory;

readonly class RemoveAdministratorCommandHandler extends AbstractAuthorisedCommandHandler implements SlackCommandHandlerInterface
{
    public function __construct(
        AuthorisationValidator $authorisationValidator,
        UnauthorisedResponseFactory $unauthorisedResponseFactory,
        private AdministratorManager $administratorManager,
        private AdministratorRemovedResponseFactory $administratorRemovedResponseFactory,
        private AdministratorNotFoundResponseFactory $administratorNotFoundResponseFactory,
        private GenericFailureResponseFactory $genericFailureResponseFactory,
    ) {
        parent::__construct($authorisationValidator, $unauthorisedResponseFactory);
    }

    public function supports(SlackCommand $command): bool
    {
        return Command::BBQ_ADMIN === $command->getCommand() && SubCommand::REMOVE_USER === $command->getSubCommand();
    }

    public function run(SlackCommand $command): void
    {
        try {
            $this->administratorManager->removeUser(
                $userId = $command->getArgumentString(CommandArgument::USER),
                $command->getTeamId(),
                $command->getAdministrator(),
            );

            $response = $this->administratorRemovedResponseFactory->create($userId);
        } catch (UnauthorisedException) {
            $response = $this->unauthorisedResponseFactory->create();
        } catch (AdministratorNotFoundException $exception) {
            $response = $this->administratorNotFoundResponseFactory->create($exception->getUserId());
        } finally {
            $response ??= $this->genericFailureResponseFactory->create();

            $command->setResponse($response);
        }
    }
}

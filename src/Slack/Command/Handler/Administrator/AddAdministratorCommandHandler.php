<?php

declare(strict_types=1);

namespace App\Slack\Command\Handler\Administrator;

use App\Service\Administrator\AdministratorManager;
use App\Service\Administrator\Exception\AdministratorExistsException;
use App\Service\Administrator\Exception\UnauthorisedException;
use App\Slack\Command\Command;
use App\Slack\Command\CommandArgument;
use App\Slack\Command\Handler\AbstractAuthorisedCommandHandler;
use App\Slack\Command\Handler\SlackCommandHandlerInterface;
use App\Slack\Command\SlackCommand;
use App\Slack\Command\SubCommand;
use App\Slack\Common\Validator\AuthorisationValidator;
use App\Slack\Response\Interaction\Factory\Administrator\AdministratorAddedResponseFactory;
use App\Slack\Response\Interaction\Factory\Administrator\AdministratorAlreadyExistsResponseFactory;
use App\Slack\Response\Interaction\Factory\Administrator\UnauthorisedResponseFactory;
use App\Slack\Response\Interaction\Factory\GenericFailureResponseFactory;

readonly class AddAdministratorCommandHandler extends AbstractAuthorisedCommandHandler implements SlackCommandHandlerInterface
{
    public function __construct(
        private AdministratorManager $manager,
        private AdministratorAddedResponseFactory $administratorAddedResponseFactory,
        private AdministratorAlreadyExistsResponseFactory $administratorAlreadyExistsResponseFactory,
        private GenericFailureResponseFactory $genericFailureResponseFactory,
        AuthorisationValidator $authorisationValidator,
        UnauthorisedResponseFactory $unauthorisedResponseFactory,
    ) {
        parent::__construct($authorisationValidator, $unauthorisedResponseFactory);
    }

    public function supports(SlackCommand $command): bool
    {
        return Command::BBQ_ADMIN === $command->getCommand()
            && SubCommand::ADD_USER === $command->getSubCommand();
    }

    public function run(SlackCommand $command): void
    {
        try {
            $administrator = $this->manager->addUser(
                $command->getArgumentString(CommandArgument::USER),
                $command->getTeamId(),
                $command->getAdministrator()
            );

            $response = $this->administratorAddedResponseFactory->create($administrator);
        } catch (UnauthorisedException) {
            $response = $this->unauthorisedResponseFactory->create();
        } catch (AdministratorExistsException $exception) {
            $response = $this->administratorAlreadyExistsResponseFactory->create($exception->getAdministrator());
        } finally {
            $response ??= $this->genericFailureResponseFactory->create();

            $command->setResponse($response);
        }
    }
}

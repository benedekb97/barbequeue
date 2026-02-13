<?php

declare(strict_types=1);

namespace App\Slack\Command\Handler\Repository;

use App\Repository\RepositoryRepositoryInterface;
use App\Slack\Command\Command;
use App\Slack\Command\CommandArgument;
use App\Slack\Command\Handler\AbstractAuthorisedCommandHandler;
use App\Slack\Command\Handler\SlackCommandHandlerInterface;
use App\Slack\Command\SlackCommand;
use App\Slack\Command\SubCommand;
use App\Slack\Common\Validator\AuthorisationValidator;
use App\Slack\Response\Interaction\Factory\Administrator\UnauthorisedResponseFactory;
use App\Slack\Response\Interaction\Factory\Repository\ConfirmRemoveRepositoryResponseFactory;
use App\Slack\Response\Interaction\Factory\Repository\UnrecognisedRepositoryResponseFactory;

readonly class RemoveRepositoryCommandHandler extends AbstractAuthorisedCommandHandler implements SlackCommandHandlerInterface
{
    public function __construct(
        AuthorisationValidator $authorisationValidator,
        UnauthorisedResponseFactory $unauthorisedResponseFactory,
        private RepositoryRepositoryInterface $repositoryRepository,
        private UnrecognisedRepositoryResponseFactory $unrecognisedRepositoryResponseFactory,
        private ConfirmRemoveRepositoryResponseFactory $confirmRemoveRepositoryResponseFactory,
    ) {
        parent::__construct($authorisationValidator, $unauthorisedResponseFactory);
    }

    public function run(SlackCommand $command): void
    {
        $repository = $this->repositoryRepository->findOneByNameAndTeamid(
            $name = $command->getArgumentString(CommandArgument::REPOSITORY),
            $teamId = $command->getTeamid(),
        );

        if (null === $repository) {
            $command->setResponse(
                $this->unrecognisedRepositoryResponseFactory->create($name, $teamId),
            );

            return;
        }

        $command->setResponse(
            $this->confirmRemoveRepositoryResponseFactory->create($repository)
        );
    }

    public function supports(SlackCommand $command): bool
    {
        return Command::BBQ_ADMIN === $command->getCommand()
            && SubCommand::REMOVE_REPOSITORY === $command->getSubCommand();
    }
}

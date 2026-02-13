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
use App\Slack\Response\Interaction\Factory\GenericFailureResponseFactory;
use App\Slack\Response\Interaction\Factory\Repository\UnrecognisedRepositoryResponseFactory;
use App\Slack\Surface\Factory\Modal\EditRepositoryModalFactory;
use App\Slack\Surface\Service\ModalService;

readonly class EditRepositoryCommandHandler extends AbstractAuthorisedCommandHandler implements SlackCommandHandlerInterface
{
    public function __construct(
        AuthorisationValidator $authorisationValidator,
        UnauthorisedResponseFactory $unauthorisedResponseFactory,
        private RepositoryRepositoryInterface $repositoryRepository,
        private EditRepositoryModalFactory $editRepositoryModalFactory,
        private ModalService $modalService,
        private UnrecognisedRepositoryResponseFactory $unrecognisedRepositoryResponseFactory,
        private GenericFailureResponseFactory $genericFailureResponseFactory,
    ) {
        parent::__construct($authorisationValidator, $unauthorisedResponseFactory);
    }

    public function run(SlackCommand $command): void
    {
        $repository = $this->repositoryRepository->findOneByNameAndTeamId(
            $name = $command->getArgumentString(CommandArgument::REPOSITORY),
            $teamId = $command->getTeamId(),
        );

        if (null === $repository) {
            $command->setResponse(
                $this->unrecognisedRepositoryResponseFactory->create($name, $teamId)
            );

            return;
        }

        $modal = $this->editRepositoryModalFactory->create($repository, $command);

        if (null === $modal) {
            $command->setResponse(
                $this->genericFailureResponseFactory->create()
            );

            return;
        }

        $this->modalService->createModal($modal, $command->getAdministrator()?->getWorkspace());
    }

    public function supports(SlackCommand $command): bool
    {
        return Command::BBQ_ADMIN === $command->getCommand()
            && SubCommand::EDIT_REPOSITORY === $command->getSubCommand();
    }
}

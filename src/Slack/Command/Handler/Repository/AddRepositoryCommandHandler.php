<?php

declare(strict_types=1);

namespace App\Slack\Command\Handler\Repository;

use App\Slack\Command\Command;
use App\Slack\Command\Handler\AbstractAuthorisedCommandHandler;
use App\Slack\Command\Handler\SlackCommandHandlerInterface;
use App\Slack\Command\SlackCommand;
use App\Slack\Command\SubCommand;
use App\Slack\Common\Validator\AuthorisationValidator;
use App\Slack\Response\Interaction\Factory\Administrator\UnauthorisedResponseFactory;
use App\Slack\Surface\Factory\Modal\AddRepositoryModalFactory;
use App\Slack\Surface\Service\ModalService;

readonly class AddRepositoryCommandHandler extends AbstractAuthorisedCommandHandler implements SlackCommandHandlerInterface
{
    public function __construct(
        AuthorisationValidator $authorisationValidator,
        UnauthorisedResponseFactory $unauthorisedResponseFactory,
        private AddRepositoryModalFactory $addRepositoryModalFactory,
        private ModalService $modalService,
    ) {
        parent::__construct($authorisationValidator, $unauthorisedResponseFactory);
    }

    public function run(SlackCommand $command): void
    {
        $workspace = $command->getAdministrator()?->getWorkspace();

        if (null === $workspace) {
            $command->setResponse($this->unauthorisedResponseFactory->create());

            return;
        }

        $modal = $this->addRepositoryModalFactory->create($command);

        if (null === $modal) {
            return;
        }

        $this->modalService->createModal($modal, $workspace);
    }

    public function supports(SlackCommand $command): bool
    {
        return Command::BBQ_ADMIN === $command->getCommand() && SubCommand::ADD_REPOSITORY === $command->getSubCommand();
    }
}

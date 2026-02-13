<?php

declare(strict_types=1);

namespace App\Slack\Command\Handler\Queue;

use App\Slack\Command\Command;
use App\Slack\Command\Handler\AbstractAuthorisedCommandHandler;
use App\Slack\Command\Handler\SlackCommandHandlerInterface;
use App\Slack\Command\SlackCommand;
use App\Slack\Command\SubCommand;
use App\Slack\Common\Validator\AuthorisationValidator;
use App\Slack\Response\Interaction\Factory\Administrator\UnauthorisedResponseFactory;
use App\Slack\Surface\Factory\Modal\AddQueueModalFactory;
use App\Slack\Surface\Service\ModalService;

readonly class OpenAddQueueModalCommandHandler extends AbstractAuthorisedCommandHandler implements SlackCommandHandlerInterface
{
    public function __construct(
        AuthorisationValidator $authorisationValidator,
        UnauthorisedResponseFactory $unauthorisedResponseFactory,
        private AddQueueModalFactory $addQueueModalFactory,
        private ModalService $modalService,
    ) {
        parent::__construct($authorisationValidator, $unauthorisedResponseFactory);
    }

    public function run(SlackCommand $command): void
    {
        $workspace = $command->getAdministrator()?->getWorkspace();

        if (null === $workspace) {
            return;
        }

        $modal = $this->addQueueModalFactory->create(null, $command, $workspace);

        if (null === $modal) {
            return;
        }

        $this->modalService->createModal($modal, $workspace);
    }

    public function supports(SlackCommand $command): bool
    {
        return Command::BBQ_ADMIN === $command->getCommand() && SubCommand::ADD_QUEUE === $command->getSubCommand();
    }
}

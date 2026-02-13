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
use App\Slack\Response\Interaction\Factory\Repository\ListRepositoriesResponseFactory;

readonly class ListRepositoriesCommandHandler extends AbstractAuthorisedCommandHandler implements SlackCommandHandlerInterface
{
    public function __construct(
        AuthorisationValidator $authorisationValidator,
        UnauthorisedResponseFactory $unauthorisedResponseFactory,
        private ListRepositoriesResponseFactory $listRepositoriesResponseFactory,
    ) {
        parent::__construct($authorisationValidator, $unauthorisedResponseFactory);
    }

    public function run(SlackCommand $command): void
    {
        if (($workspace = $command->getAdministrator()?->getWorkspace()) === null) {
            $command->setREsponse(
                $this->unauthorisedResponseFactory->create()
            );

            return;
        }

        $command->setResponse(
            $this->listRepositoriesResponseFactory->create($workspace),
        );
    }

    public function supports(SlackCommand $command): bool
    {
        return Command::BBQ_ADMIN === $command->getCommand()
            && SubCommand::LIST_REPOSITORIES === $command->getSubCommand();
    }
}

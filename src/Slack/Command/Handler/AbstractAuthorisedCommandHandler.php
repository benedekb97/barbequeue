<?php

declare(strict_types=1);

namespace App\Slack\Command\Handler;

use App\Slack\Command\SlackCommand;
use App\Slack\Common\Component\Exception\UnauthorisedUserException;
use App\Slack\Common\Validator\AuthorisationValidator;
use App\Slack\Response\Interaction\Factory\Administrator\UnauthorisedResponseFactory;

abstract readonly class AbstractAuthorisedCommandHandler implements SlackCommandHandlerInterface
{
    public function __construct(
        private AuthorisationValidator $authorisationValidator,
        protected UnauthorisedResponseFactory $unauthorisedResponseFactory,
    ) {
    }

    public function handle(SlackCommand $command): void
    {
        try {
            $administrator = $this->authorisationValidator->validate(
                $command->getCommand(),
                $command->getUserId(),
                $command->getTeamId(),
            );

            $command->setAdministrator($administrator);

            $this->run($command);
        } catch (UnauthorisedUserException) {
            $command->setResponse(
                $this->unauthorisedResponseFactory->create()
            );
        }
    }

    abstract public function run(SlackCommand $command): void;
}

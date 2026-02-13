<?php

declare(strict_types=1);

namespace App\Slack\Interaction\Handler;

use App\Slack\Common\Component\Exception\UnauthorisedUserException;
use App\Slack\Common\Validator\AuthorisationValidator;
use App\Slack\Interaction\SlackInteraction;
use App\Slack\Response\Interaction\Factory\Administrator\UnauthorisedResponseFactory;

abstract readonly class AbstractAuthorisedInteractionHandler implements SlackInteractionHandlerInterface
{
    public function __construct(
        private AuthorisationValidator $authorisationValidator,
        protected UnauthorisedResponseFactory $unauthorisedResponseFactory,
    ) {
    }

    public function handle(SlackInteraction $interaction): void
    {
        try {
            $administrator = $this->authorisationValidator->validate(
                $interaction->getInteraction(),
                $interaction->getUserId(),
                $interaction->getTeamId(),
            );

            $interaction->setAdministrator($administrator);

            $this->run($interaction);
        } catch (UnauthorisedUserException) {
            $interaction->setResponse(
                $this->unauthorisedResponseFactory->create()
            );
        }
    }

    abstract public function run(SlackInteraction $interaction): void;
}

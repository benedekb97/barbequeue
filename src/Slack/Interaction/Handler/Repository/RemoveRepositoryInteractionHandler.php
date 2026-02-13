<?php

declare(strict_types=1);

namespace App\Slack\Interaction\Handler\Repository;

use App\Service\Administrator\Exception\UnauthorisedException;
use App\Service\Repository\Exception\RepositoryNotFoundException;
use App\Service\Repository\RepositoryManager;
use App\Slack\Common\Validator\AuthorisationValidator;
use App\Slack\Interaction\Handler\AbstractAuthorisedInteractionHandler;
use App\Slack\Interaction\Handler\SlackInteractionHandlerInterface;
use App\Slack\Interaction\Interaction;
use App\Slack\Interaction\InteractionType;
use App\Slack\Interaction\SlackInteraction;
use App\Slack\Response\Interaction\Factory\Administrator\UnauthorisedResponseFactory;
use App\Slack\Response\Interaction\Factory\GenericFailureResponseFactory;
use App\Slack\Response\Interaction\Factory\Repository\RemoveRepositoryCancelledResponseFactory;
use App\Slack\Response\Interaction\Factory\Repository\RepositoryAlreadyRemovedResponseFactory;
use App\Slack\Response\Interaction\Factory\Repository\RepositoryRemovedResponseFactory;

readonly class RemoveRepositoryInteractionHandler extends AbstractAuthorisedInteractionHandler implements SlackInteractionHandlerInterface
{
    public function __construct(
        AuthorisationValidator $authorisationValidator,
        UnauthorisedResponseFactory $unauthorisedResponseFactory,
        private RemoveRepositoryCancelledResponseFactory $removeRepositoryCancelledResponseFactory,
        private RepositoryManager $repositoryManager,
        private RepositoryRemovedResponseFactory $repositoryRemovedResponseFactory,
        private RepositoryAlreadyRemovedResponseFactory $repositoryAlreadyRemovedResponseFactory,
        private GenericFailureResponseFactory $genericFailureResponseFactory,
    ) {
        parent::__construct($authorisationValidator, $unauthorisedResponseFactory);
    }

    public function run(SlackInteraction $interaction): void
    {
        if ('no' === $interaction->getValue()) {
            $interaction->setResponse(
                $this->removeRepositoryCancelledResponseFactory->create()
            );

            return;
        }

        try {
            $name = $this->repositoryManager->removeRepository(
                (int) $interaction->getValue(),
                $interaction->getAdministrator()?->getWorkspace()
            );

            $response = $this->repositoryRemovedResponseFactory->create($name);
        } catch (UnauthorisedException) {
            $response = $this->unauthorisedResponseFactory->create();
        } catch (RepositoryNotFoundException) {
            $response = $this->repositoryAlreadyRemovedResponseFactory->create();
        } finally {
            $response ??= $this->genericFailureResponseFactory->create();

            $interaction->setResponse($response);
        }
    }

    public function supports(SlackInteraction $interaction): bool
    {
        return Interaction::REMOVE_REPOSITORY === $interaction->getInteraction()
            && InteractionType::BLOCK_ACTIONS === $interaction->getType();
    }
}

<?php

declare(strict_types=1);

namespace App\Slack\Interaction\Handler\Repository;

use App\Service\Administrator\Exception\UnauthorisedException;
use App\Service\Repository\Exception\RepositoryAlreadyExistsException;
use App\Service\Repository\RepositoryManager;
use App\Slack\Common\Validator\AuthorisationValidator;
use App\Slack\Interaction\Handler\AbstractAuthorisedInteractionHandler;
use App\Slack\Interaction\Handler\SlackInteractionHandlerInterface;
use App\Slack\Interaction\Interaction;
use App\Slack\Interaction\InteractionType;
use App\Slack\Interaction\SlackInteraction;
use App\Slack\Interaction\SlackViewSubmission;
use App\Slack\Response\Interaction\Factory\Administrator\UnauthorisedResponseFactory;
use App\Slack\Response\Interaction\Factory\Repository\RepositoryAddedResponseFactory;
use App\Slack\Response\Interaction\Factory\Repository\RepositoryAlreadyExistsResponseFactory;
use App\Slack\Response\PrivateMessage\NoResponse;
use App\Slack\Surface\Component\ModalArgument;

readonly class AddRepositoryInteractionHandler extends AbstractAuthorisedInteractionHandler implements SlackInteractionHandlerInterface
{
    public function __construct(
        AuthorisationValidator $authorisationValidator,
        UnauthorisedResponseFactory $unauthorisedResponseFactory,
        private RepositoryManager $repositoryManager,
        private RepositoryAddedResponseFactory $repositoryAddedResponseFactory,
        private RepositoryAlreadyExistsResponseFactory $repositoryAlreadyExistsResponseFactory,
    ) {
        parent::__construct($authorisationValidator, $unauthorisedResponseFactory);
    }

    public function run(SlackInteraction $interaction): void
    {
        if (!$interaction instanceof SlackViewSubmission) {
            return;
        }

        try {
            $repository = $this->repositoryManager->addRepository(
                (string) $interaction->getArgumentString(ModalArgument::REPOSITORY_NAME->value),
                $interaction->getArgumentString(ModalArgument::REPOSITORY_URL->value),
                $interaction->getArgumentIntArray(ModalArgument::REPOSITORY_BLOCKS->value),
                $interaction->getAdministrator()?->getWorkspace(),
            );

            $response = $this->repositoryAddedResponseFactory->create($repository);
        } catch (UnauthorisedException) {
            $response = $this->unauthorisedResponseFactory->create();
        } catch (RepositoryAlreadyExistsException $exception) {
            $response = $this->repositoryAlreadyExistsResponseFactory->create($exception->getName());
        } finally {
            $interaction->setResponse($response ?? new NoResponse());
        }
    }

    public function supports(SlackInteraction $interaction): bool
    {
        return Interaction::ADD_REPOSITORY === $interaction->getInteraction()
            && InteractionType::VIEW_SUBMISSION === $interaction->getType();
    }
}

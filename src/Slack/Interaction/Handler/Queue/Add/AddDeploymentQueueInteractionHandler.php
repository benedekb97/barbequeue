<?php

declare(strict_types=1);

namespace App\Slack\Interaction\Handler\Queue\Add;

use App\Event\HomeTabUpdatedEvent;
use App\Factory\DeploymentQueueFactory;
use App\Slack\Common\Validator\AuthorisationValidator;
use App\Slack\Interaction\Handler\AbstractAuthorisedInteractionHandler;
use App\Slack\Interaction\Handler\SlackInteractionHandlerInterface;
use App\Slack\Interaction\Interaction;
use App\Slack\Interaction\InteractionType;
use App\Slack\Interaction\SlackInteraction;
use App\Slack\Interaction\SlackViewSubmission;
use App\Slack\Response\Interaction\Factory\Administrator\UnauthorisedResponseFactory;
use App\Slack\Response\Interaction\Factory\Queue\Add\DeploymentQueueAddedResponseFactory;
use App\Slack\Surface\Component\ModalArgument;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

readonly class AddDeploymentQueueInteractionHandler extends AbstractAuthorisedInteractionHandler implements SlackInteractionHandlerInterface
{
    public function __construct(
        AuthorisationValidator $authorisationValidator,
        UnauthorisedResponseFactory $unauthorisedResponseFactory,
        private DeploymentQueueFactory $deploymentQueueFactory,
        private EntityManagerInterface $entityManager,
        private EventDispatcherInterface $eventDispatcher,
        private DeploymentQueueAddedResponseFactory $deploymentQueueAddedResponseFactory,
    ) {
        parent::__construct($authorisationValidator, $unauthorisedResponseFactory);
    }

    public function run(SlackInteraction $interaction): void
    {
        if (!$interaction instanceof SlackViewSubmission) {
            return;
        }

        $queue = $this->deploymentQueueFactory->create(
            (string) $interaction->getArgumentString(ModalArgument::QUEUE_NAME->value),
            $interaction->getAdministrator()?->getWorkspace(),
            $interaction->getArgumentInteger(ModalArgument::QUEUE_MAXIMUM_ENTRIES_PER_USER->value),
            $interaction->getArgumentInteger(ModalArgument::QUEUE_EXPIRY_MINUTES->value),
            (array) $interaction->getArgumentIntArray(ModalArgument::QUEUE_REPOSITORIES->value),
            (string) $interaction->getArgumentString(ModalArgument::QUEUE_BEHAVIOUR->value),
        );

        $this->entityManager->persist($queue);
        $this->entityManager->flush();

        $this->eventDispatcher->dispatch(new HomeTabUpdatedEvent($interaction->getUserId(), $interaction->getTeamId()));

        $interaction->setResponse($this->deploymentQueueAddedResponseFactory->create($queue));
    }

    public function supports(SlackInteraction $interaction): bool
    {
        return Interaction::ADD_DEPLOYMENT_QUEUE === $interaction->getInteraction()
            && InteractionType::VIEW_SUBMISSION === $interaction->getType();
    }
}

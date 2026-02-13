<?php

declare(strict_types=1);

namespace App\Slack\Interaction\Handler\Queue\Add;

use App\Event\HomeTabUpdatedEvent;
use App\Factory\QueueFactory;
use App\Slack\Common\Validator\AuthorisationValidator;
use App\Slack\Interaction\Handler\AbstractAuthorisedInteractionHandler;
use App\Slack\Interaction\Handler\SlackInteractionHandlerInterface;
use App\Slack\Interaction\Interaction;
use App\Slack\Interaction\InteractionType;
use App\Slack\Interaction\SlackInteraction;
use App\Slack\Interaction\SlackViewSubmission;
use App\Slack\Response\Interaction\Factory\Administrator\UnauthorisedResponseFactory;
use App\Slack\Response\Interaction\Factory\Queue\Add\QueueAddedResponseFactory;
use App\Slack\Surface\Component\ModalArgument;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

readonly class AddSimpleQueueInteractionHandler extends AbstractAuthorisedInteractionHandler implements SlackInteractionHandlerInterface
{
    public function __construct(
        AuthorisationValidator $authorisationValidator,
        UnauthorisedResponseFactory $unauthorisedResponseFactory,
        private QueueFactory $queueFactory,
        private EntityManagerInterface $entityManager,
        private EventDispatcherInterface $eventDispatcher,
        private QueueAddedResponseFactory $queueAddedResponseFactory,
    ) {
        parent::__construct($authorisationValidator, $unauthorisedResponseFactory);
    }

    public function run(SlackInteraction $interaction): void
    {
        if (!$interaction instanceof SlackViewSubmission) {
            return;
        }

        $queue = $this->queueFactory->create(
            (string) $interaction->getArgumentString(ModalArgument::QUEUE_NAME->value),
            $interaction->getAdministrator()?->getWorkspace(),
            $interaction->getArgumentInteger(ModalArgument::QUEUE_MAXIMUM_ENTRIES_PER_USER->value),
            $interaction->getArgumentInteger(ModalArgument::QUEUE_EXPIRY_MINUTES->value),
        );

        $this->entityManager->persist($queue);
        $this->entityManager->flush();

        $this->eventDispatcher->dispatch(new HomeTabUpdatedEvent($interaction->getUserId(), $interaction->getTeamId()));

        $interaction->setResponse($this->queueAddedResponseFactory->create($queue));
    }

    public function supports(SlackInteraction $interaction): bool
    {
        return Interaction::ADD_SIMPLE_QUEUE === $interaction->getInteraction()
            && InteractionType::VIEW_SUBMISSION === $interaction->getType();
    }
}

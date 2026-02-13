<?php

declare(strict_types=1);

namespace App\Slack\Interaction\Handler\Queue\Edit;

use App\Service\Queue\Edit\EditQueueContext;
use App\Service\Queue\Edit\EditQueueHandler;
use App\Service\Repository\Exception\RepositoryNotFoundException;
use App\Slack\Common\Validator\AuthorisationValidator;
use App\Slack\Interaction\Handler\AbstractAuthorisedInteractionHandler;
use App\Slack\Interaction\Handler\SlackInteractionHandlerInterface;
use App\Slack\Interaction\Interaction;
use App\Slack\Interaction\InteractionType;
use App\Slack\Interaction\SlackInteraction;
use App\Slack\Interaction\SlackViewSubmission;
use App\Slack\Response\Interaction\Factory\Administrator\UnauthorisedResponseFactory;
use App\Slack\Response\Interaction\Factory\Queue\Edit\QueueEditedResponseFactory;
use App\Slack\Surface\Component\ModalArgument;
use Doctrine\ORM\EntityNotFoundException;
use Psr\Log\LoggerInterface;

readonly class EditQueueInteractionHandler extends AbstractAuthorisedInteractionHandler implements SlackInteractionHandlerInterface
{
    public function __construct(
        private LoggerInterface $logger,
        private QueueEditedResponseFactory $queueEditedMessageFactory,
        AuthorisationValidator $authorisationValidator,
        UnauthorisedResponseFactory $unauthorisedResponseFactory,
        private EditQueueHandler $editQueueHandler,
    ) {
        parent::__construct($authorisationValidator, $unauthorisedResponseFactory);
    }

    public function supports(SlackInteraction $interaction): bool
    {
        return in_array($interaction->getInteraction(), [
            Interaction::EDIT_QUEUE,
            Interaction::EDIT_QUEUE_DEPLOYMENT,
        ], true)
            && InteractionType::VIEW_SUBMISSION === $interaction->getType();
    }

    public function run(SlackInteraction $interaction): void
    {
        if (!$interaction instanceof SlackViewSubmission) {
            return;
        }

        try {
            $context = new EditQueueContext(
                (int) $interaction->getArgumentInteger(ModalArgument::QUEUE->value),
                $interaction->getTeamId(),
                $interaction->getUserId(),
                $interaction->getArgumentInteger(ModalArgument::QUEUE_MAXIMUM_ENTRIES_PER_USER->value),
                $interaction->getArgumentInteger(ModalArgument::QUEUE_EXPIRY_MINUTES->value),
                $interaction->getArgumentIntArray(ModalArgument::QUEUE_REPOSITORIES->value),
                $interaction->getArgumentString(ModalArgument::QUEUE_BEHAVIOUR->value),
            );

            $this->editQueueHandler->handle($context);

            $interaction->setResponse($this->queueEditedMessageFactory->create($context->getQueue()));
        } catch (EntityNotFoundException|RepositoryNotFoundException $e) {
            $this->logger->debug($e->getMessage());
        } finally {
            $interaction->setHandled();
        }
    }
}

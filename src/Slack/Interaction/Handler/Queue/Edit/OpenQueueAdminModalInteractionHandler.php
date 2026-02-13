<?php

declare(strict_types=1);

namespace App\Slack\Interaction\Handler\Queue\Edit;

use App\Repository\QueueRepositoryInterface;
use App\Slack\Common\Validator\AuthorisationValidator;
use App\Slack\Interaction\Handler\AbstractAuthorisedInteractionHandler;
use App\Slack\Interaction\Handler\SlackInteractionHandlerInterface;
use App\Slack\Interaction\Interaction;
use App\Slack\Interaction\InteractionType;
use App\Slack\Interaction\SlackInteraction;
use App\Slack\Response\Interaction\Factory\Administrator\UnauthorisedResponseFactory;
use App\Slack\Response\Interaction\Factory\Queue\Common\UnrecognisedQueueResponseFactory;
use App\Slack\Response\PrivateMessage\NoResponse;
use App\Slack\Surface\Factory\Modal\EditQueueModalFactory;
use App\Slack\Surface\Service\ModalService;

readonly class OpenQueueAdminModalInteractionHandler extends AbstractAuthorisedInteractionHandler implements SlackInteractionHandlerInterface
{
    public function __construct(
        private QueueRepositoryInterface $queueRepository,
        private UnrecognisedQueueResponseFactory $unrecognisedQueueResponseFactory,
        private ModalService $modalService,
        AuthorisationValidator $authorisationValidator,
        UnauthorisedResponseFactory $unauthorisedResponseFactory,
        private EditQueueModalFactory $editQueueModalFactory,
    ) {
        parent::__construct($authorisationValidator, $unauthorisedResponseFactory);
    }

    public function supports(SlackInteraction $interaction): bool
    {
        return Interaction::EDIT_QUEUE_ACTION === $interaction->getInteraction()
            && InteractionType::BLOCK_ACTIONS === $interaction->getType();
    }

    public function run(SlackInteraction $interaction): void
    {
        $queue = $this->queueRepository->find((int) $interaction->getValue());

        if (null === $queue) {
            $interaction->setResponse(
                $this->unrecognisedQueueResponseFactory->create('', '', withActions: false)
            );

            return;
        }

        $modal = $this->editQueueModalFactory->create($queue, $interaction);

        if (null !== $modal) {
            $this->modalService->createModal($modal, $queue->getWorkspace());
        }

        $interaction->setResponse(new NoResponse());
    }
}

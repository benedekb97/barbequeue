<?php

declare(strict_types=1);

namespace App\Slack\Interaction\Handler\Queue\Add;

use App\Enum\Queue;
use App\Slack\Common\Validator\AuthorisationValidator;
use App\Slack\Interaction\Handler\AbstractAuthorisedInteractionHandler;
use App\Slack\Interaction\Handler\SlackInteractionHandlerInterface;
use App\Slack\Interaction\Interaction;
use App\Slack\Interaction\InteractionType;
use App\Slack\Interaction\SlackInteraction;
use App\Slack\Response\Interaction\Factory\Administrator\UnauthorisedResponseFactory;
use App\Slack\Surface\Factory\Modal\AddQueueModalFactory;
use App\Slack\Surface\Service\ModalService;

readonly class QueueTypeSelectionInteractionHandler extends AbstractAuthorisedInteractionHandler implements SlackInteractionHandlerInterface
{
    public function __construct(
        AuthorisationValidator $authorisationValidator,
        UnauthorisedResponseFactory $unauthorisedResponseFactory,
        private AddQueueModalFactory $addQueueModalFactory,
        private ModalService $modalService,
    ) {
        parent::__construct($authorisationValidator, $unauthorisedResponseFactory);
    }

    public function run(SlackInteraction $interaction): void
    {
        $queueType = Queue::tryFrom($interaction->getValue());

        $workspace = $interaction->getAdministrator()?->getWorkspace();

        if (null === $workspace) {
            return;
        }

        $modal = $this->addQueueModalFactory->create($queueType, $interaction, $workspace);

        $modalId = $interaction->getViewId() ?? '';

        if (null === $modal) {
            return;
        }

        $this->modalService->updateModal($modal, $workspace, $modalId);
    }

    public function supports(SlackInteraction $interaction): bool
    {
        return Interaction::QUEUE_TYPE === $interaction->getInteraction()
            && InteractionType::BLOCK_ACTIONS === $interaction->getType();
    }
}

<?php

declare(strict_types=1);

namespace App\Slack\Interaction\Handler;

use App\Repository\QueueRepositoryInterface;
use App\Slack\Interaction\Component\SlackInteraction;
use App\Slack\Interaction\Interaction;
use App\Slack\Response\Common\NoResponse;
use App\Slack\Response\Interaction\Factory\UnrecognisedQueueResponseFactory;
use App\Slack\Surface\Service\ModalService;

readonly class OpenQueueAdminModalInteractionHandler implements SlackInteractionHandlerInterface
{
    public function __construct(
        private QueueRepositoryInterface $queueRepository,
        private UnrecognisedQueueResponseFactory $unrecognisedQueueResponseFactory,
        private ModalService $modalService,
    ) {
    }

    public function supports(SlackInteraction $interaction): bool
    {
        return $interaction->getInteraction() === Interaction::EDIT_QUEUE_ACTION;
    }

    public function handle(SlackInteraction $interaction): void
    {
        $queue = $this->queueRepository->find((int) $interaction->getValue());

        if ($queue === null) {
            $interaction->setResponse(
                $this->unrecognisedQueueResponseFactory->create('', '', withActions: false)
            );

            return;
        }

        $this->modalService->createQueueModal($queue, $interaction);

        $interaction->setResponse(new NoResponse());
    }
}

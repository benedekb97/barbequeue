<?php

declare(strict_types=1);

namespace App\Slack\Interaction\Handler\Queue\Join;

use App\Service\Queue\Exception\DeploymentInformationRequiredException;
use App\Service\Queue\Exception\QueueNotFoundException;
use App\Service\Queue\Exception\UnableToJoinQueueException;
use App\Service\Queue\Join\JoinQueueContext;
use App\Service\Queue\Join\JoinQueueHandler;
use App\Slack\Interaction\Handler\SlackInteractionHandlerInterface;
use App\Slack\Interaction\Interaction;
use App\Slack\Interaction\InteractionType;
use App\Slack\Interaction\SlackInteraction;
use App\Slack\Response\Interaction\Factory\GenericFailureResponseFactory;
use App\Slack\Response\Interaction\Factory\Queue\Common\UnrecognisedQueueResponseFactory;
use App\Slack\Response\Interaction\Factory\Queue\Join\QueueJoinedResponseFactory;
use App\Slack\Response\Interaction\Factory\Queue\Join\UnableToJoinQueueResponseFactory;
use App\Slack\Response\PrivateMessage\NoResponse;
use App\Slack\Surface\Factory\Modal\JoinQueueModalFactory;
use App\Slack\Surface\Service\ModalService;

readonly class JoinQueueInteractionHandler implements SlackInteractionHandlerInterface
{
    public function __construct(
        private QueueJoinedResponseFactory $queueJoinedResponseFactory,
        private UnableToJoinQueueResponseFactory $unableToJoinQueueResponseFactory,
        private UnrecognisedQueueResponseFactory $unrecognisedQueueResponseFactory,
        private GenericFailureResponseFactory $genericFailureResponseFactory,
        private JoinQueueModalFactory $joinQueueModalFactory,
        private ModalService $modalService,
        private JoinQueueHandler $joinQueueHandler,
    ) {
    }

    public function supports(SlackInteraction $interaction): bool
    {
        return Interaction::JOIN_QUEUE === $interaction->getInteraction()
            && InteractionType::BLOCK_ACTIONS === $interaction->getType();
    }

    public function handle(SlackInteraction $interaction): void
    {
        try {
            $context = new JoinQueueContext(
                $interaction->getValue(),
                $interaction->getTeamId(),
                $interaction->getUserId(),
                $interaction->getUserName(),
            );

            $this->joinQueueHandler->handle($context);

            $response = $this->queueJoinedResponseFactory->create($context->getQueuedUser());
        } catch (QueueNotFoundException $exception) {
            $response = $this->unrecognisedQueueResponseFactory->create(
                $exception->getQueueName(),
                $exception->getTeamId(),
                $exception->getUserId(),
            );
        } catch (UnableToJoinQueueException $exception) {
            $response = $this->unableToJoinQueueResponseFactory->create($exception->getQueue());
        } catch (DeploymentInformationRequiredException $exception) {
            $modal = $this->joinQueueModalFactory->create($queue = $exception->getQueue(), $interaction);

            if (null !== $modal) {
                $this->modalService->createModal($modal, $queue->getWorkspace());
            }

            $response = new NoResponse();
        } finally {
            $response ??= $this->genericFailureResponseFactory->create();

            $interaction->setResponse($response);
        }
    }
}

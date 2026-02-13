<?php

declare(strict_types=1);

namespace App\Slack\Interaction\Handler\Queue\Leave;

use App\Service\Queue\Exception\LeaveQueueInformationRequiredException;
use App\Service\Queue\Exception\QueueNotFoundException;
use App\Service\Queue\Exception\UnableToLeaveQueueException;
use App\Service\Queue\Leave\LeaveQueueContext;
use App\Service\Queue\Leave\LeaveQueueHandler;
use App\Slack\Interaction\Handler\SlackInteractionHandlerInterface;
use App\Slack\Interaction\SlackInteraction;
use App\Slack\Response\Interaction\Factory\GenericFailureResponseFactory;
use App\Slack\Response\Interaction\Factory\Queue\Common\UnrecognisedQueueResponseFactory;
use App\Slack\Response\Interaction\Factory\Queue\Leave\QueueLeftResponseFactory;
use App\Slack\Response\Interaction\Factory\Queue\Leave\UnableToLeaveQueueResponseFactory;
use App\Slack\Response\PrivateMessage\NoResponse;
use App\Slack\Surface\Factory\Modal\LeaveQueueModalFactory;
use App\Slack\Surface\Service\ModalService;
use Psr\Log\LoggerInterface;

abstract readonly class AbstractLeaveQueueInteractionHandler implements SlackInteractionHandlerInterface
{
    public function __construct(
        private UnrecognisedQueueResponseFactory $unrecognisedQueueResponseFactory,
        private UnableToLeaveQueueResponseFactory $unableToLeaveQueueResponseFactory,
        private QueueLeftResponseFactory $queueLeftResponseFactory,
        private GenericFailureResponseFactory $genericFailureResponseFactory,
        private LeaveQueueHandler $leaveQueueHandler,
        private LeaveQueueModalFactory $leaveQueueModalFactory,
        private ModalService $modalService,
        private LoggerInterface $logger,
    ) {
    }

    public function handle(SlackInteraction $interaction): void
    {
        try {
            $context = $this->getContext($interaction);

            $this->leaveQueueHandler->handle($context);

            $response = $this->queueLeftResponseFactory->create($context->getQueue(), $context->getUserId());
        } catch (QueueNotFoundException $exception) {
            $response = $this->unrecognisedQueueResponseFactory->create(
                $exception->getQueueName(),
                $exception->getTeamId(),
                $exception->getUserId()
            );
        } catch (UnableToLeaveQueueException $exception) {
            $response = $this->unableToLeaveQueueResponseFactory->create($exception->getQueue());
        } catch (LeaveQueueInformationRequiredException $exception) {
            $modal = $this->leaveQueueModalFactory->create($queue = $exception->getQueue(), $interaction);

            if (null !== $modal) {
                $this->modalService->createModal($modal, $queue->getWorkspace());
            }

            $response = new NoResponse();
        } catch (\BadMethodCallException) {
            $this->logger->warning('Failed to create context for view submission');
        } finally {
            $response ??= $this->genericFailureResponseFactory->create();

            $interaction->setResponse($response);
        }
    }

    /** @throws \BadMethodCallException */
    abstract public function getContext(SlackInteraction $interaction): LeaveQueueContext;
}

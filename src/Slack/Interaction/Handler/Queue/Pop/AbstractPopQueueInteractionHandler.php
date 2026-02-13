<?php

declare(strict_types=1);

namespace App\Slack\Interaction\Handler\Queue\Pop;

use App\Service\Queue\Exception\PopQueueInformationRequiredException;
use App\Service\Queue\Exception\QueueNotFoundException;
use App\Service\Queue\Pop\PopQueueContext;
use App\Service\Queue\Pop\PopQueueHandler;
use App\Slack\Common\Validator\AuthorisationValidator;
use App\Slack\Interaction\Handler\AbstractAuthorisedInteractionHandler;
use App\Slack\Interaction\Handler\SlackInteractionHandlerInterface;
use App\Slack\Interaction\SlackInteraction;
use App\Slack\Response\Interaction\Factory\Administrator\UnauthorisedResponseFactory;
use App\Slack\Response\Interaction\Factory\Queue\Common\UnrecognisedQueueResponseFactory;
use App\Slack\Response\Interaction\Factory\Queue\QueuePoppedResponseFactory;
use App\Slack\Response\PrivateMessage\NoResponse;
use App\Slack\Surface\Factory\Modal\PopQueueModalFactory;
use App\Slack\Surface\Service\ModalService;
use Psr\Log\LoggerInterface;

abstract readonly class AbstractPopQueueInteractionHandler extends AbstractAuthorisedInteractionHandler implements SlackInteractionHandlerInterface
{
    public function __construct(
        AuthorisationValidator $authorisationValidator,
        UnauthorisedResponseFactory $unauthorisedResponseFactory,
        private UnrecognisedQueueResponseFactory $unrecognisedQueueResponseFactory,
        private QueuePoppedResponseFactory $queuePoppedResponseFactory,
        private PopQueueHandler $popQueueHandler,
        private PopQueueModalFactory $popQueueModalFactory,
        private ModalService $modalService,
        private LoggerInterface $logger,
    ) {
        parent::__construct($authorisationValidator, $unauthorisedResponseFactory);
    }

    public function run(SlackInteraction $interaction): void
    {
        try {
            $context = $this->getContext($interaction);

            $this->popQueueHandler->handle($context);

            $response = $this->queuePoppedResponseFactory->create($context->getQueue());
        } catch (QueueNotFoundException $exception) {
            $response = $this->unrecognisedQueueResponseFactory->create(
                $exception->getQueueName(),
                $exception->getTeamId(),
                null,
                false,
            );
        } catch (PopQueueInformationRequiredException $exception) {
            $modal = $this->popQueueModalFactory->create($queue = $exception->getQueue(), $interaction);

            if (null !== $modal) {
                $this->modalService->createModal($modal, $queue->getWorkspace());
            }
        } catch (\BadMethodCallException) {
            $this->logger->warning('Failed to create context for view submission');
        } finally {
            $response ??= new NoResponse();

            $interaction->setResponse($response);
        }
    }

    /** @throws \BadMethodCallException */
    abstract public function getContext(SlackInteraction $interaction): PopQueueContext;
}

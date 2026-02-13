<?php

declare(strict_types=1);

namespace App\Slack\Interaction\Handler\Queue\Join;

use App\Service\Queue\Exception\DeploymentInformationRequiredException;
use App\Service\Queue\Exception\InvalidDeploymentUrlException;
use App\Service\Queue\Exception\QueueNotFoundException;
use App\Service\Queue\Exception\UnableToJoinQueueException;
use App\Service\Queue\Join\JoinQueueContext;
use App\Service\Queue\Join\JoinQueueHandler;
use App\Slack\Interaction\Handler\SlackInteractionHandlerInterface;
use App\Slack\Interaction\Interaction;
use App\Slack\Interaction\InteractionType;
use App\Slack\Interaction\SlackInteraction;
use App\Slack\Interaction\SlackViewSubmission;
use App\Slack\Response\Interaction\Factory\Queue\Join\InvalidDeploymentUrlResponseFactory;
use App\Slack\Response\Interaction\Factory\Queue\Join\QueueJoinedResponseFactory;
use App\Slack\Response\Interaction\Factory\Queue\Join\UnableToJoinQueueResponseFactory;
use App\Slack\Response\PrivateMessage\NoResponse;
use App\Slack\Surface\Component\ModalArgument;
use Psr\Log\LoggerInterface;

readonly class JoinDeploymentQueueInteractionHandler implements SlackInteractionHandlerInterface
{
    public function __construct(
        private LoggerInterface $logger,
        private QueueJoinedResponseFactory $queueJoinedResponseFactory,
        private UnableToJoinQueueResponseFactory $unableToJoinQueueResponseFactory,
        private JoinQueueHandler $joinQueueHandler,
        private InvalidDeploymentUrlResponseFactory $invalidDeploymentUrlResponseFactory,
    ) {
    }

    public function supports(SlackInteraction $interaction): bool
    {
        return Interaction::JOIN_QUEUE_DEPLOYMENT === $interaction->getInteraction()
            && InteractionType::VIEW_SUBMISSION === $interaction->getType();
    }

    public function handle(SlackInteraction $interaction): void
    {
        if (!$interaction instanceof SlackViewSubmission) {
            return;
        }

        try {
            $context = new JoinQueueContext(
                (string) $interaction->getArgumentString(ModalArgument::JOIN_QUEUE_NAME->value),
                $interaction->getTeamId(),
                $interaction->getUserId(),
                $interaction->getUserName(),
                $interaction->getArgumentInteger(ModalArgument::JOIN_QUEUE_REQUIRED_MINUTES->value),
                $interaction->getArgumentString(ModalArgument::DEPLOYMENT_DESCRIPTION->value),
                $interaction->getArgumentString(ModalArgument::DEPLOYMENT_LINK->value),
                $interaction->getArgumentInteger(ModalArgument::DEPLOYMENT_REPOSITORY->value),
                $interaction->getArgumentStringArray(ModalArgument::DEPLOYMENT_NOTIFY_USERS->value) ?? [],
            );

            $this->joinQueueHandler->handle($context);

            $response = $this->queueJoinedResponseFactory->create($context->getQueuedUser());
        } catch (QueueNotFoundException $exception) {
            $this->logger->warning('A queue called {queueName} could not be found when joining the queue.', [
                'queueName' => $exception->getQueueName(),
            ]);
        } catch (UnableToJoinQueueException $exception) {
            $response = $this->unableToJoinQueueResponseFactory->create($exception->getQueue());
        } catch (DeploymentInformationRequiredException) {
            $this->logger->warning('DeploymentInformationRequiredException thrown after submitting join deployment queue modal');
        } catch (InvalidDeploymentUrlException $exception) {
            $response = $this->invalidDeploymentUrlResponseFactory->create(
                $exception->getDeploymentLink(),
                $exception->getQueue()
            );
        } finally {
            $response ??= new NoResponse();

            $interaction->setResponse($response);
            $interaction->setHandled();
        }
    }
}

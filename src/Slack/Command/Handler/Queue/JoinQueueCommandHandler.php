<?php

declare(strict_types=1);

namespace App\Slack\Command\Handler\Queue;

use App\Service\Queue\Exception\DeploymentInformationRequiredException;
use App\Service\Queue\Exception\QueueNotFoundException;
use App\Service\Queue\Exception\UnableToJoinQueueException;
use App\Service\Queue\Join\JoinQueueContext;
use App\Service\Queue\Join\JoinQueueHandler;
use App\Slack\Command\Command;
use App\Slack\Command\CommandArgument;
use App\Slack\Command\Handler\SlackCommandHandlerInterface;
use App\Slack\Command\SlackCommand;
use App\Slack\Command\SubCommand;
use App\Slack\Response\Interaction\Factory\GenericFailureResponseFactory;
use App\Slack\Response\Interaction\Factory\Queue\Common\UnrecognisedQueueResponseFactory;
use App\Slack\Response\Interaction\Factory\Queue\Join\QueueJoinedResponseFactory;
use App\Slack\Response\Interaction\Factory\Queue\Join\UnableToJoinQueueResponseFactory;
use App\Slack\Response\PrivateMessage\NoResponse;
use App\Slack\Surface\Factory\Modal\JoinQueueModalFactory;
use App\Slack\Surface\Service\ModalService;

readonly class JoinQueueCommandHandler implements SlackCommandHandlerInterface
{
    public function __construct(
        private UnrecognisedQueueResponseFactory $unrecognisedQueueResponseFactory,
        private UnableToJoinQueueResponseFactory $unableToJoinQueueResponseFactory,
        private QueueJoinedResponseFactory $queueJoinedResponseFactory,
        private GenericFailureResponseFactory $genericFailureResponseFactory,
        private JoinQueueModalFactory $joinQueueModalFactory,
        private ModalService $modalService,
        private JoinQueueHandler $joinQueueHandler,
    ) {
    }

    public function supports(SlackCommand $command): bool
    {
        return Command::BBQ === $command->getCommand() && SubCommand::JOIN === $command->getSubCommand();
    }

    public function handle(SlackCommand $command): void
    {
        try {
            $context = new JoinQueueContext(
                $command->getArgumentString(CommandArgument::QUEUE),
                $command->getTeamId(),
                $command->getUserId(),
                $command->getUserName(),
                $command->getOptionalArgumentInteger(CommandArgument::TIME),
            );

            $this->joinQueueHandler->handle($context);

            $response = $this->queueJoinedResponseFactory->create($context->getQueuedUser());
        } catch (QueueNotFoundException $exception) {
            $response = $this->unrecognisedQueueResponseFactory->create(
                $exception->getQueueName(),
                $exception->getTeamId(),
                $exception->getUserId()
            );
        } catch (UnableToJoinQueueException $exception) {
            $response = $this->unableToJoinQueueResponseFactory->create($exception->getQueue());
        } catch (DeploymentInformationRequiredException $exception) {
            $modal = $this->joinQueueModalFactory->create($queue = $exception->getQueue(), $command);

            if (null !== $modal) {
                $this->modalService->createModal($modal, $queue->getWorkspace());
            }

            $response = new NoResponse();
        } finally {
            $response ??= $this->genericFailureResponseFactory->create();

            $command->setResponse($response);
        }
    }
}

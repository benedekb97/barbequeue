<?php

declare(strict_types=1);

namespace App\Slack\Command\Handler\Queue;

use App\Service\Queue\Exception\LeaveQueueInformationRequiredException;
use App\Service\Queue\Exception\QueueNotFoundException;
use App\Service\Queue\Exception\UnableToLeaveQueueException;
use App\Service\Queue\Leave\LeaveQueueContext;
use App\Service\Queue\Leave\LeaveQueueHandler;
use App\Slack\Command\Command;
use App\Slack\Command\CommandArgument;
use App\Slack\Command\Handler\SlackCommandHandlerInterface;
use App\Slack\Command\SlackCommand;
use App\Slack\Command\SubCommand;
use App\Slack\Response\Interaction\Factory\GenericFailureResponseFactory;
use App\Slack\Response\Interaction\Factory\Queue\Common\UnrecognisedQueueResponseFactory;
use App\Slack\Response\Interaction\Factory\Queue\Leave\QueueLeftResponseFactory;
use App\Slack\Response\Interaction\Factory\Queue\Leave\UnableToLeaveQueueResponseFactory;
use App\Slack\Response\PrivateMessage\NoResponse;
use App\Slack\Surface\Factory\Modal\LeaveQueueModalFactory;
use App\Slack\Surface\Service\ModalService;

readonly class LeaveQueueCommandHandler implements SlackCommandHandlerInterface
{
    public function __construct(
        private UnrecognisedQueueResponseFactory $unrecognisedQueueResponseFactory,
        private UnableToLeaveQueueResponseFactory $unableToLeaveQueueResponseFactory,
        private QueueLeftResponseFactory $queueLeftResponseFactory,
        private GenericFailureResponseFactory $genericFailureResponseFactory,
        private LeaveQueueHandler $leaveQueueHandler,
        private LeaveQueueModalFactory $leaveQueueModalFactory,
        private ModalService $modalService,
    ) {
    }

    public function supports(SlackCommand $command): bool
    {
        return Command::BBQ === $command->getCommand() && SubCommand::LEAVE === $command->getSubCommand();
    }

    public function handle(SlackCommand $command): void
    {
        try {
            $context = new LeaveQueueContext(
                $command->getArgumentString(CommandArgument::QUEUE),
                $command->getTeamId(),
                $userId = $command->getUserId()
            );

            $this->leaveQueueHandler->handle($context);

            $response = $this->queueLeftResponseFactory->create($context->getQueue(), $userId);
        } catch (QueueNotFoundException $exception) {
            $response = $this->unrecognisedQueueResponseFactory->create(
                $exception->getQueueName(),
                $exception->getTeamId(),
                $exception->getUserId()
            );
        } catch (UnableToLeaveQueueException $exception) {
            $response = $this->unableToLeaveQueueResponseFactory->create($exception->getQueue());
        } catch (LeaveQueueInformationRequiredException $exception) {
            $modal = $this->leaveQueueModalFactory->create($queue = $exception->getQueue(), $command);

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

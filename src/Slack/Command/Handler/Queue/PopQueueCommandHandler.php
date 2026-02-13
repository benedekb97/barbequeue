<?php

declare(strict_types=1);

namespace App\Slack\Command\Handler\Queue;

use App\Service\Queue\Exception\PopQueueInformationRequiredException;
use App\Service\Queue\Exception\QueueNotFoundException;
use App\Service\Queue\Pop\PopQueueContext;
use App\Service\Queue\Pop\PopQueueHandler;
use App\Slack\Command\Command;
use App\Slack\Command\CommandArgument;
use App\Slack\Command\Handler\AbstractAuthorisedCommandHandler;
use App\Slack\Command\Handler\SlackCommandHandlerInterface;
use App\Slack\Command\SlackCommand;
use App\Slack\Command\SubCommand;
use App\Slack\Common\Validator\AuthorisationValidator;
use App\Slack\Response\Interaction\Factory\Administrator\UnauthorisedResponseFactory;
use App\Slack\Response\Interaction\Factory\Queue\Common\UnrecognisedQueueResponseFactory;
use App\Slack\Response\Interaction\Factory\Queue\QueuePoppedResponseFactory;
use App\Slack\Response\PrivateMessage\NoResponse;
use App\Slack\Surface\Factory\Modal\PopQueueModalFactory;
use App\Slack\Surface\Service\ModalService;

readonly class PopQueueCommandHandler extends AbstractAuthorisedCommandHandler implements SlackCommandHandlerInterface
{
    public function __construct(
        private UnrecognisedQueueResponseFactory $unrecognisedQueueResponseFactory,
        private QueuePoppedResponseFactory $queuePoppedResponseFactory,
        AuthorisationValidator $authorisationValidator,
        UnauthorisedResponseFactory $unauthorisedResponseFactory,
        private PopQueueHandler $popQueueHandler,
        private PopQueueModalFactory $popQueueModalFactory,
        private ModalService $modalService,
    ) {
        parent::__construct($authorisationValidator, $unauthorisedResponseFactory);
    }

    public function supports(SlackCommand $command): bool
    {
        return Command::BBQ_ADMIN === $command->getCommand() && SubCommand::POP_QUEUE === $command->getSubCommand();
    }

    public function run(SlackCommand $command): void
    {
        try {
            $context = new PopQueueContext(
                $command->getArgumentString(CommandArgument::QUEUE),
                $command->getTeamId(),
                $command->getUserId(),
            );

            $this->popQueueHandler->handle($context);

            $response = $this->queuePoppedResponseFactory->create($context->getQueue());
        } catch (QueueNotFoundException $exception) {
            $response = $this->unrecognisedQueueResponseFactory->create(
                $exception->getQueueName(),
                $exception->getTeamId(),
                null,
                false
            );
        } catch (PopQueueInformationRequiredException $exception) {
            $modal = $this->popQueueModalFactory->create($queue = $exception->getQueue(), $command);

            if (null !== $modal) {
                $this->modalService->createModal($modal, $queue->getWorkspace());
            }

            $response = new NoResponse();
        } finally {
            $response ??= null;

            $command->setResponse($response);
        }
    }
}

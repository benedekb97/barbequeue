<?php

declare(strict_types=1);

namespace App\Slack\Command\Handler\Queue;

use App\Repository\QueueRepositoryInterface;
use App\Slack\Command\Command;
use App\Slack\Command\CommandArgument;
use App\Slack\Command\Handler\AbstractAuthorisedCommandHandler;
use App\Slack\Command\Handler\SlackCommandHandlerInterface;
use App\Slack\Command\SlackCommand;
use App\Slack\Command\SubCommand;
use App\Slack\Common\Validator\AuthorisationValidator;
use App\Slack\Response\Interaction\Factory\Administrator\UnauthorisedResponseFactory;
use App\Slack\Response\Interaction\Factory\GenericFailureResponseFactory;
use App\Slack\Response\Interaction\Factory\Queue\Common\UnrecognisedQueueResponseFactory;
use App\Slack\Surface\Factory\Modal\EditQueueModalFactory;
use App\Slack\Surface\Service\ModalService;

readonly class OpenQueueAdminModalCommandHandler extends AbstractAuthorisedCommandHandler implements SlackCommandHandlerInterface
{
    public function __construct(
        private QueueRepositoryInterface $queueRepository,
        private UnrecognisedQueueResponseFactory $unrecognisedQueueResponseFactory,
        private ModalService $modalService,
        AuthorisationValidator $authorisationValidator,
        UnauthorisedResponseFactory $unauthorisedResponseFactory,
        private EditQueueModalFactory $editQueueModalFactory,
        private GenericFailureResponseFactory $genericFailureResponseFactory,
    ) {
        parent::__construct($authorisationValidator, $unauthorisedResponseFactory);
    }

    public function supports(SlackCommand $command): bool
    {
        return Command::BBQ_ADMIN === $command->getCommand() && SubCommand::EDIT_QUEUE === $command->getSubCommand();
    }

    public function run(SlackCommand $command): void
    {
        $queue = $this->queueRepository->findOneByNameAndTeamId(
            $queueName = $command->getArgumentString(CommandArgument::QUEUE),
            $teamId = $command->getTeamId()
        );

        if (null === $queue) {
            $command->setResponse(
                $this->unrecognisedQueueResponseFactory->create($queueName, $teamId, withActions: false)
            );

            return;
        }

        $modal = $this->editQueueModalFactory->create($queue, $command);

        if (null === $modal) {
            $command->setResponse(
                $this->genericFailureResponseFactory->create()
            );

            return;
        }

        $this->modalService->createModal($modal, $queue->getWorkspace());
    }
}

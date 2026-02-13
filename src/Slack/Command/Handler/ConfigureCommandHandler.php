<?php

declare(strict_types=1);

namespace App\Slack\Command\Handler;

use App\Repository\WorkspaceRepositoryInterface;
use App\Resolver\UserResolver;
use App\Slack\Command\Command;
use App\Slack\Command\SlackCommand;
use App\Slack\Command\SubCommand;
use App\Slack\Response\Interaction\Factory\GenericFailureResponseFactory;
use App\Slack\Response\PrivateMessage\NoResponse;
use App\Slack\Surface\Factory\Modal\ConfigurationModalFactory;
use App\Slack\Surface\Service\ModalService;

readonly class ConfigureCommandHandler implements SlackCommandHandlerInterface
{
    public function __construct(
        private UserResolver $userResolver,
        private WorkspaceRepositoryInterface $workspaceRepository,
        private ConfigurationModalFactory $configurationModalFactory,
        private ModalService $modalService,
        private GenericFailureResponseFactory $genericFailureResponseFactory,
    ) {
    }

    public function supports(SlackCommand $command): bool
    {
        return Command::BBQ === $command->getCommand() && SubCommand::CONFIGURE === $command->getSubCommand();
    }

    public function handle(SlackCommand $command): void
    {
        $workspace = $this->workspaceRepository->findOneBy([
            'slackId' => $command->getTeamId(),
        ]);

        if (null === $workspace) {
            $command->setResponse($this->genericFailureResponseFactory->create());

            return;
        }

        $user = $this->userResolver->resolve($command->getUserId(), $workspace);

        $modal = $this->configurationModalFactory->create($user, $command);

        if (null === $modal) {
            $command->setResponse($this->genericFailureResponseFactory->create());

            return;
        }

        $this->modalService->createModal($modal, $workspace);

        $command->setResponse(new NoResponse());
    }
}

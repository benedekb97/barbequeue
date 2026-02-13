<?php

declare(strict_types=1);

namespace App\Slack\Event\Handler;

use App\Slack\Event\Component\AppHomeOpenedEvent;
use App\Slack\Event\Component\SlackEventInterface;
use App\Slack\Surface\Factory\Exception\WorkspaceNotFoundException;
use App\Slack\Surface\Factory\Home\HomeViewFactory;
use App\Slack\Surface\Service\HomeService;
use Psr\Log\LoggerInterface;

readonly class AppHomeOpenedEventHandler implements SlackEventHandlerInterface
{
    public function __construct(
        private LoggerInterface $logger,
        private HomeViewFactory $homeViewFactory,
        private HomeService $homeService,
    ) {
    }

    public function supports(SlackEventInterface $event): bool
    {
        return $event instanceof AppHomeOpenedEvent;
    }

    public function handle(SlackEventInterface $event): void
    {
        if (!$event instanceof AppHomeOpenedEvent) {
            return;
        }

        if ('home' !== $event->getTab()) {
            return;
        }

        try {
            $view = $this->homeViewFactory->create(
                $event->getUserId(),
                $event->getTeamId(),
                $event->isFirstTime(),
            );

            $this->homeService->publish($view);
        } catch (WorkspaceNotFoundException $exception) {
            $this->logger->alert('Received event for unknown workspace {workspaceId}', [
                'workspaceId' => $exception->getWorkspaceId(),
            ]);
        }
    }
}

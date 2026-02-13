<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Event\HomeTabUpdatedEvent;
use App\Slack\Surface\Factory\Home\HomeViewFactory;
use App\Slack\Surface\Service\HomeService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

readonly class HomeTabEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private HomeViewFactory $homeViewFactory,
        private HomeService $homeService,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            HomeTabUpdatedEvent::class => ['handleUpdated'],
        ];
    }

    public function handleUpdated(HomeTabUpdatedEvent $event): void
    {
        $view = $this->homeViewFactory->create($event->getUserId(), $event->getTeamId(), false);

        $this->homeService->publish($view);
    }
}

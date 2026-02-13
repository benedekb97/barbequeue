<?php

declare(strict_types=1);

namespace App\Slack\Event\Factory;

use App\Slack\Event\Component\AppHomeOpenedEvent;
use App\Slack\Event\Component\SlackEventInterface;
use App\Slack\Event\Event;
use Symfony\Component\HttpFoundation\Request;

readonly class AppHomeOpenedSlackEventFactory implements SlackEventFactoryInterface
{
    public function supports(Event $event): bool
    {
        return Event::APP_HOME_OPENED === $event;
    }

    public function create(Request $request): SlackEventInterface
    {
        return new AppHomeOpenedEvent(
            $this->getUserId($request),
            $this->getTeamId($request),
            $this->getChannel($request),
            $this->getTab($request),
            $this->isFirstTime($request),
        );
    }

    private function getUserId(Request $request): string
    {
        /** @var array{user: string|null} $event */
        $event = $request->request->all('event');

        return $event['user'] ?? '';
    }

    private function getTeamId(Request $request): string
    {
        return $request->request->getString('team_id');
    }

    private function getChannel(Request $request): string
    {
        /** @var array{channel: string|null} $event */
        $event = $request->request->all('event');

        return $event['channel'] ?? '';
    }

    private function getTab(Request $request): string
    {
        /** @var array{tab: string|null} $event */
        $event = $request->request->all('event');

        return $event['tab'] ?? '';
    }

    private function isFirstTime(Request $request): bool
    {
        $event = $request->request->all('event');

        return !(array_key_exists('view', $event) && null !== $event['view']);
    }
}

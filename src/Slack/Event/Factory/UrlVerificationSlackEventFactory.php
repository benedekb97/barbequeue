<?php

declare(strict_types=1);

namespace App\Slack\Event\Factory;

use App\Slack\Event\Component\SlackEventInterface;
use App\Slack\Event\Component\UrlVerificationEvent;
use App\Slack\Event\Event;
use Symfony\Component\HttpFoundation\Request;

readonly class UrlVerificationSlackEventFactory implements SlackEventFactoryInterface
{
    public function supports(Event $event): bool
    {
        return Event::URL_VERIFICATION === $event;
    }

    public function create(Request $request): SlackEventInterface
    {
        return new UrlVerificationEvent(
            $this->getToken($request),
            $this->getChallenge($request),
        );
    }

    private function getToken(Request $request): string
    {
        return $request->request->getString('token');
    }

    private function getChallenge(Request $request): string
    {
        return $request->request->getString('challenge');
    }
}

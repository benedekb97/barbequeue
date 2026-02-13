<?php

declare(strict_types=1);

namespace App\Slack\Event\Resolver;

use App\Slack\Event\Event;
use App\Slack\Event\Exception\UnrecognisedEventException;
use Symfony\Component\HttpFoundation\Request;

readonly class EventTypeResolver
{
    /** @throws UnrecognisedEventException */
    public function resolve(Request $request): Event
    {
        $type = $request->request->getString('type');

        if (empty($type)) {
            throw new UnrecognisedEventException();
        }

        if ('event_callback' === $type) {
            /** @var array{type?: string} $event */
            $event = $request->request->all('event');

            if (!array_key_exists('type', $event)) {
                throw new UnrecognisedEventException();
            }

            $type = $event['type'];
        }

        $event = Event::tryFrom($type);

        if (null === $event) {
            throw new UnrecognisedEventException($type);
        }

        return $event;
    }
}

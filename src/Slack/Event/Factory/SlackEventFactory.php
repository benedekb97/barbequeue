<?php

declare(strict_types=1);

namespace App\Slack\Event\Factory;

use App\Slack\Event\Component\SlackEventInterface;
use App\Slack\Event\Exception\UnhandledEventException;
use App\Slack\Event\Exception\UnrecognisedEventException;
use App\Slack\Event\Resolver\EventTypeResolver;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\HttpFoundation\Request;

readonly class SlackEventFactory
{
    public function __construct(
        /** @var SlackEventFactoryInterface[] $factories */
        #[AutowireIterator(SlackEventFactoryInterface::TAG)]
        private iterable $factories,
        private EventTypeResolver $eventTypeResolver,
    ) {
    }

    /** @throws UnrecognisedEventException|UnhandledEventException */
    public function create(Request $request): SlackEventInterface
    {
        $type = $this->eventTypeResolver->resolve($request);

        foreach ($this->factories as $factory) {
            if ($factory->supports($type)) {
                return $factory->create($request);
            }
        }

        throw new UnhandledEventException($type);
    }
}

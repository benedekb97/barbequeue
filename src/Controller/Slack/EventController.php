<?php

declare(strict_types=1);

namespace App\Controller\Slack;

use App\Message\Slack\SlackEventMessage;
use App\Slack\Event\Component\UrlVerificationEvent;
use App\Slack\Event\Exception\UnhandledEventException;
use App\Slack\Event\Exception\UnrecognisedEventException;
use App\Slack\Event\Factory\SlackEventFactory;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

readonly class EventController
{
    public function __construct(
        private SlackEventFactory $eventFactory,
        private LoggerInterface $logger,
        private MessageBusInterface $messageBus,
    ) {
    }

    #[Route('/slack/event', methods: [Request::METHOD_POST])]
    public function __invoke(Request $request): Response
    {
        try {
            $event = $this->eventFactory->create($request);
        } catch (UnrecognisedEventException $exception) {
            $this->logger->debug('Unrecognised event exception received with {type}.', [
                'type' => $exception->getType(),
            ]);

            return new Response();
        } catch (UnhandledEventException $exception) {
            $this->logger->debug('Unhandled event exception received while trying to parse {event}.', [
                'event' => $exception->getEvent()->value,
            ]);

            return new Response();
        }

        $this->logger->debug('Event parsed successfully as {class}.', [
            'class' => $event::class,
        ]);

        if ($event instanceof UrlVerificationEvent) {
            $this->logger->debug('URL Verification event received with {challenge}', [
                'challenge' => $challenge = $event->getChallenge(),
            ]);

            return new JsonResponse([
                'challenge' => $challenge,
            ]);
        }

        $this->messageBus->dispatch(new SlackEventMessage($event));

        return new Response();
    }
}

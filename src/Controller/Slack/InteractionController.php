<?php

declare(strict_types=1);

namespace App\Controller\Slack;

use App\Message\Slack\SlackInteractionMessage;
use App\Slack\Interaction\Exception\UnhandledInteractionTypeException;
use App\Slack\Interaction\Factory\SlackInteractionFactory;
use App\Slack\Interaction\SlackViewSubmission;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

class InteractionController implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private readonly SlackInteractionFactory $interactionFactory,
        private readonly MessageBusInterface $messageBus,
    ) {
    }

    #[Route('/slack/interaction', methods: [Request::METHOD_POST])]
    public function __invoke(Request $request): Response
    {
        try {
            $interaction = $this->interactionFactory->create($request);
        } catch (UnhandledInteractionTypeException $exception) {
            $this->logger?->warning('Received unhandled interaction type {interaction}.', [
                'interaction' => $exception->getInteractionType()->value,
            ]);

            return new Response();
        } catch (\ValueError $error) {
            $this->logger?->warning('Could not resolve requested interaction. {message}', [
                'message' => $error->getMessage(),
            ]);

            return new Response();
        }

        $this->logger?->info('Dispatching interaction to asynchronous handler: {interaction} {type} {arguments}', [
            'interaction' => $interaction->getInteraction()->value,
            'type' => $interaction->getType()->value,
            'arguments' => match (true) {
                $interaction instanceof SlackViewSubmission => $this->normaliseArguments($interaction->getArguments()),
                default => $interaction->getValue(),
            },
        ]);

        $this->messageBus->dispatch(new SlackInteractionMessage($interaction));

        return new Response();
    }

    /** @param (int|string|int[]|string[]|null)[] $arguments */
    private function normaliseArguments(array $arguments): string
    {
        return implode(', ', array_map(function ($argument) {
            if (is_array($argument)) {
                return '['.implode(', ', $argument).']';
            }

            return (string) $argument;
        }, $arguments));
    }
}

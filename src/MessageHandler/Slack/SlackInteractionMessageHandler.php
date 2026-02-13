<?php

declare(strict_types=1);

namespace App\MessageHandler\Slack;

use App\Message\Slack\SlackInteractionMessage;
use App\Slack\Interaction\Handler\SlackInteractionHandlerInterface;
use App\Slack\Response\Interaction\InteractionResponseHandler;
use App\Slack\Response\Interaction\SlackInteractionResponse;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class SlackInteractionMessageHandler
{
    /** @param SlackInteractionHandlerInterface[] $handlers */
    public function __construct(
        #[AutowireIterator(SlackInteractionHandlerInterface::TAG)]
        /** @var SlackInteractionHandlerInterface[] $handlers */
        private iterable $handlers,
        private LoggerInterface $logger,
        private InteractionResponseHandler $interactionResponseHandler,
    ) {
    }

    public function __invoke(SlackInteractionMessage $message): void
    {
        $interaction = $message->getInteraction();

        foreach ($this->handlers as $handler) {
            if ($interaction->isPending() && $handler->supports($interaction)) {
                $handler->handle($interaction);

                $interaction->setHandled();
            }
        }

        if ($interaction->isPending() && !empty($interaction->getResponseUrl())) {
            $this->logger->error('Unhandled interaction: {interaction} {type}', [
                'interaction' => $interaction->getInteraction(),
                'type' => $interaction->getType(),
            ]);
        }

        if (($response = $interaction->getResponse()) instanceof SlackInteractionResponse) {
            $this->logger->debug('Sending response');

            $this->interactionResponseHandler->handle($interaction->getResponseUrl(), $response);
        }
    }
}

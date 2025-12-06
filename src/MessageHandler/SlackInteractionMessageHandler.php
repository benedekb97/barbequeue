<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\SlackInteractionMessage;
use App\Slack\Interaction\Handler\SlackInteractionHandlerInterface;
use App\Slack\Interaction\InteractionResponseHandler;
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
            }
        }

        if ($interaction->isPending()) {
            $this->logger->error('Unhandled interaction: '.$interaction::class);
        }

        if (($response = $interaction->getResponse()) instanceof SlackInteractionResponse) {
            $this->interactionResponseHandler->handle($interaction->getResponseUrl(), $response);
        }
    }
}

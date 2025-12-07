<?php

declare(strict_types=1);

namespace App\MessageHandler\Slack;

use App\Message\Slack\SlackCommandMessage;
use App\Slack\Command\Handler\SlackCommandHandlerInterface;
use App\Slack\Response\Interaction\InteractionResponseHandler;
use App\Slack\Response\Interaction\SlackInteractionResponse;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class SlackCommandMessageHandler
{
    public function __construct(
        #[AutowireIterator(SlackCommandHandlerInterface::TAG)]
        /** @var SlackCommandHandlerInterface[] $handlers */
        private iterable $handlers,
        private InteractionResponseHandler $interactionResponseHandler,
    ) {
    }

    public function __invoke(SlackCommandMessage $message): void
    {
        $command = $message->getCommand();

        /** @var SlackCommandHandlerInterface $handler */
        foreach ($this->handlers as $handler) {
            if ($handler->supports($command) && $command->isPending()) {
                $handler->handle($command);
            }
        }

        if (($commandResponse = $command->getResponse()) instanceof SlackInteractionResponse) {
            $this->interactionResponseHandler->handle($command->getResponseUrl(), $commandResponse);
        }
    }
}

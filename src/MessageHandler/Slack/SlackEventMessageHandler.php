<?php

declare(strict_types=1);

namespace App\MessageHandler\Slack;

use App\Message\Slack\SlackEventMessage;
use App\Slack\Event\Handler\SlackEventHandlerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class SlackEventMessageHandler
{
    public function __construct(
        /** @var SlackEventHandlerInterface[] $handlers */
        #[AutowireIterator(SlackEventHandlerInterface::TAG)]
        private iterable $handlers,
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(SlackEventMessage $message): void
    {
        $event = $message->getEvent();

        foreach ($this->handlers as $handler) {
            if ($handler->supports($event)) {
                $handler->handle($event);

                return;
            }
        }

        $this->logger->warning('Unhandled event {type}.', [
            'type' => $event->getType()->value,
        ]);
    }
}

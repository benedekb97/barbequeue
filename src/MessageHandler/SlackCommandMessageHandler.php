<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\SlackCommandMessage;
use App\Slack\Command\Handler\SlackCommandHandlerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class SlackCommandMessageHandler
{
    public function __construct(
        #[AutowireIterator(SlackCommandHandlerInterface::TAG)]
        /** @var SlackCommandHandlerInterface[] $handlers */
        private iterable $handlers,
    ) {}

    public function __invoke(SlackCommandMessage $message): void
    {
        $command = $message->getCommand();

        /** @var SlackCommandHandlerInterface $handler */
        foreach ($this->handlers as $handler) {
            if ($handler->supports($command) && $command->isPending()) {
                $handler->handle($command);
            }
        }
    }
}

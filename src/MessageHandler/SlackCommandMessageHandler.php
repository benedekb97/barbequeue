<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\SlackCommandMessage;
use App\Slack\Command\Handler\SlackCommandHandlerInterface;
use App\Slack\Response\Command\SlackCommandResponse;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\HttpClient\Exception\ServerException;
use Symfony\Component\HttpClient\Exception\TransportException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;

#[AsMessageHandler]
readonly class SlackCommandMessageHandler
{
    public function __construct(
        #[AutowireIterator(SlackCommandHandlerInterface::TAG)]
        /** @var SlackCommandHandlerInterface[] $handlers */
        private iterable $handlers,
        private HttpClientInterface $httpClient,
        private LoggerInterface $logger,
    ) {}

    public function __invoke(SlackCommandMessage $message): void
    {
        $command = $message->getCommand();

        $this->logger->debug('command '.$command->getCommand()->value);
        $this->logger->debug('subcommand '.$command->getSubCommand()?->value);

        /** @var SlackCommandHandlerInterface $handler */
        foreach ($this->handlers as $handler) {
            if ($handler->supports($command) && $command->isPending()) {
                $this->logger->debug('Command handled by '.$handler::class);

                $handler->handle($command);
            }
        }

        if (($response = $command->getResponse()) instanceof SlackCommandResponse) {
            try {
                $response = $this->httpClient->request('POST', $command->getResponseUrl(), [
                    'body' => $response->toArray(),
                ]);

                $this->logger->debug($response->getContent());
            } catch (ServerException $e) {
                $this->logger->debug($e->getMessage());
            }
        }
    }
}

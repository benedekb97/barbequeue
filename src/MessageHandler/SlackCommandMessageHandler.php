<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\SlackCommandMessage;
use App\Slack\Command\Handler\SlackCommandHandlerInterface;
use App\Slack\Response\Command\SlackCommandResponse;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Contracts\HttpClient\HttpClientInterface;

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

        /** @var SlackCommandHandlerInterface $handler */
        foreach ($this->handlers as $handler) {
            if ($handler->supports($command) && $command->isPending()) {
                $this->logger->debug('Command handled by '.$handler::class);

                $handler->handle($command);
            }
        }

        if (($response = $command->getResponse()) instanceof SlackCommandResponse) {
            $response = $this->httpClient->request('POST', $command->getResponseUrl(), [
                'body' => json_encode($response->toArray()),
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
            ]);

            $this->logger->debug($response->getContent());
        }
    }
}

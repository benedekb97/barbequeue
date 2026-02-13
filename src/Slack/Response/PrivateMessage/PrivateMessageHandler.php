<?php

declare(strict_types=1);

namespace App\Slack\Response\PrivateMessage;

use App\Slack\Client\Exception\UnauthorisedClientException;
use App\Slack\Response\PrivateMessage\Handler\PrivateMessageHandlerInterface;
use JoliCode\Slack\Exception\SlackErrorResponse;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

readonly class PrivateMessageHandler
{
    public function __construct(
        private LoggerInterface $logger,
        /** @var PrivateMessageHandlerInterface[] $handlers */
        #[AutowireIterator(PrivateMessageHandlerInterface::class)]
        private iterable $handlers,
    ) {
    }

    public function handle(SlackPrivateMessage $response): void
    {
        try {
            foreach ($this->handlers as $handler) {
                if ($handler->supports($response)) {
                    $handler->handle($response);
                }
            }
        } catch (SlackErrorResponse $exception) {
            $this->logger->debug($exception->getMessage());

            $metadata = json_encode($exception->getResponseMetadata());

            if (false !== $metadata) {
                $this->logger->debug($metadata);
            }
        } catch (UnauthorisedClientException $exception) {
            $this->logger->error($exception->getMessage());
        } catch (\Throwable $exception) {
            $this->logger->debug($exception->getMessage());
            $this->logger->debug($exception::class);
        }
    }
}

<?php

declare(strict_types=1);

namespace App\Slack\Surface\Service;

use App\Entity\Queue;
use App\Slack\Block\Component\SectionBlock;
use App\Slack\Command\Component\SlackCommand;
use App\Slack\Surface\Component\ModalSurface;
use JoliCode\Slack\Api\Client;
use JoliCode\Slack\ClientFactory;
use JoliCode\Slack\Exception\SlackErrorResponse;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\Exception\ServerException;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;

readonly class ModalService
{
    private Client $client;

    public function __construct(
        private LoggerInterface $logger,
        string $slackAccessToken,
    ) {
        $this->client = ClientFactory::create($slackAccessToken);
    }

    public function createQueueModal(Queue $queue, SlackCommand $command): void
    {
        $modal = new ModalSurface(
            $command->getTriggerId(),
            sprintf('Edit the %s queue', $queue->getName()),
            [
                new SectionBlock('test'),
            ],
            'edit-queue-'.$queue->getName().'-'.$command->getUserId(),
            'Cancel',
            'Save',
        );

        try {
            $response = $this->client->viewsOpen($modal->toArray());
        } catch (ServerException|HttpExceptionInterface $exception) {
            $response = $exception->getResponse();

            $this->logger->error($exception->getMessage());
            $this->logger->error($response->getContent(false));
        } catch (SlackErrorResponse $exception) {
            $this->logger->error($exception->getMessage());
            $this->logger->error(json_encode($exception->getResponseMetadata()));
        } catch (\Throwable $exception) {
            $this->logger->error($exception->getMessage());
            $this->logger->error($exception::class);
            $this->logger->error($exception->getTraceAsString());

            return;
        }

        $this->logger->debug($response->getContent(false));
    }
}

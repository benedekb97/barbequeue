<?php

declare(strict_types=1);

namespace App\Slack\Surface\Service;

use App\Entity\Queue;
use App\Slack\Block\Component\SectionBlock;
use App\Slack\Command\Component\SlackCommand;
use App\Slack\Surface\Component\ModalSurface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\Exception\ServerException;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

readonly class ModalService
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private LoggerInterface $logger,
        private string $slackAccessToken,
    ) {
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
            submitDisabled: true,
        );

        try {
            $response = $this->httpClient->request('POST', 'https://slack.com/api/views.open', [
                'headers' => [
                    'Authorization' => 'Bearer '.$this->slackAccessToken,
                    'Content-Type' => 'application/json',
                ],
                'body' => json_encode($modal->toArray(), JSON_UNESCAPED_SLASHES),
            ]);
        } catch (ServerException|HttpExceptionInterface $exception) {
            $response = $exception->getResponse();

            $this->logger->error($exception->getMessage());
            $this->logger->error($response->getContent(false));
        } catch (\Throwable $exception) {
            $this->logger->error($exception->getMessage());
            $this->logger->error($exception::class);
        }

        $this->logger->debug($response->getContent(false));
    }
}

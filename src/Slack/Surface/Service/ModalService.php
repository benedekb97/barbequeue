<?php

declare(strict_types=1);

namespace App\Slack\Surface\Service;

use App\Entity\Workspace;
use App\Slack\Client\Exception\UnauthorisedClientException;
use App\Slack\Client\Factory\ClientFactory;
use App\Slack\Surface\Component\ModalSurface;
use JoliCode\Slack\Exception\SlackErrorResponse;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\Exception\ServerException;

readonly class ModalService
{
    public function __construct(
        private LoggerInterface $logger,
        private ClientFactory $clientFactory,
    ) {
    }

    public function createModal(ModalSurface $modal, ?Workspace $workspace): void
    {
        try {
            $this->clientFactory->create(
                $workspace?->getBotToken() ?? throw new UnauthorisedClientException($workspace)
            )->viewsOpen($modal->toArray());
        } catch (SlackErrorResponse $exception) {
            $this->logger->error($exception->getMessage());

            $metadata = json_encode($exception->getResponseMetadata());

            if (false !== $metadata) {
                $this->logger->error($metadata);
            }
        } catch (ServerException $exception) {
            $response = $exception->getResponse();

            $this->logger->error($exception->getMessage());
            $this->logger->error($response->getContent(false));
        } catch (UnauthorisedClientException $exception) {
            $this->logger->error($exception->getMessage());
        } catch (\Throwable $exception) {
            $this->logger->error($exception->getMessage());
            $this->logger->error($exception::class);
            $this->logger->error($exception->getTraceAsString());

            return;
        }
    }

    public function updateModal(ModalSurface $modal, ?Workspace $workspace, string $modalId): void
    {
        try {
            $this->clientFactory->create(
                $workspace?->getBotToken() ?? throw new UnauthorisedClientException($workspace)
            )->viewsUpdate(array_filter(array_merge(
                $modal->toArray(),
                [
                    'view_id' => $modalId,
                    'trigger_id' => null,
                ],
            )));
        } catch (SlackErrorResponse $exception) {
            $this->logger->error($exception->getMessage());

            $metadata = json_encode($exception->getResponseMetadata());

            if (false !== $metadata) {
                $this->logger->error($metadata);
            }
        } catch (ServerException $exception) {
            $response = $exception->getResponse();

            $this->logger->error($exception->getMessage());
            $this->logger->error($response->getContent(false));
        } catch (UnauthorisedClientException $exception) {
            $this->logger->error($exception->getMessage());
        } catch (\Throwable $exception) {
            $this->logger->error($exception->getMessage());
            $this->logger->error($exception::class);
            $this->logger->error($exception->getTraceAsString());

            return;
        }
    }
}

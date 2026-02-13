<?php

declare(strict_types=1);

namespace App\Slack\Surface\Service;

use App\Slack\Client\Exception\UnauthorisedClientException;
use App\Slack\Client\Factory\ClientFactory;
use App\Slack\Surface\Component\HomeSurface;
use JoliCode\Slack\Exception\SlackErrorResponse;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\Exception\ServerException;

readonly class HomeService
{
    public function __construct(
        private ClientFactory $clientFactory,
        private LoggerInterface $logger,
    ) {
    }

    public function publish(HomeSurface $home): void
    {
        $workspace = $home->getWorkspace();

        try {
            $this->clientFactory
                ->create($workspace->getBotToken() ?? throw new UnauthorisedClientException($workspace))
                ->viewsPublish($home->toArray());
        } catch (UnauthorisedClientException $exception) {
            $this->logger->error('{workspace} has no bot token set. Please reinstall application via OAuth', [
                'workspace' => $exception->getWorkspace()?->getSlackId(),
            ]);
        } catch (SlackErrorResponse $exception) {
            /** @var array|null $metadata */
            $metadata = $exception->getResponseMetadata();

            $this->logger->error($exception->getMessage(), $metadata ?? []);
        } catch (ServerException $exception) {
            $response = $exception->getResponse();

            $this->logger->error($exception->getMessage());
            $this->logger->error($response->getContent(false));
        } catch (\Throwable $exception) {
            $this->logger->error($exception->getMessage());
            $this->logger->error($exception::class);
            $this->logger->error($exception->getTraceAsString());

            return;
        }
    }
}

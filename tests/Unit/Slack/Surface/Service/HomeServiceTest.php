<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Surface\Service;

use App\Entity\Workspace;
use App\Slack\Client\Factory\ClientFactory;
use App\Slack\Surface\Component\HomeSurface;
use App\Slack\Surface\Service\HomeService;
use App\Tests\Unit\LoggerAwareTestCase;
use JoliCode\Slack\Api\Client;
use JoliCode\Slack\Exception\SlackErrorResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(HomeService::class)]
class HomeServiceTest extends LoggerAwareTestCase
{
    #[Test]
    public function itShouldLogErrorWhenWorkspaceBotTokenNotSet(): void
    {
        $workspace = $this->createMock(Workspace::class);
        $workspace->expects($this->once())
            ->method('getBotToken')
            ->willReturn(null);

        $workspace->expects($this->once())
            ->method('getSlackId')
            ->willReturn($slackId = 'slackId');

        $surface = $this->createMock(HomeSurface::class);
        $surface->expects($this->once())
            ->method('getWorkspace')
            ->willReturn($workspace);

        $this->expectsError('{workspace} has no bot token set. Please reinstall application via OAuth', [
            'workspace' => $slackId,
        ]);

        $service = new HomeService(
            $this->createStub(ClientFactory::class),
            $this->getLogger(),
        );

        $service->publish($surface);
    }

    #[Test]
    public function itShouldLogSlackErrorResponse(): void
    {
        $exception = new SlackErrorResponse('errorCode', $metadata = ['metadata']);

        $this->expectsError('Slack returned error code "errorCode"', $metadata);

        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('viewsPublish')
            ->with($surfaceValue = [])
            ->willThrowException($exception);

        $workspace = $this->createMock(Workspace::class);
        $workspace->expects($this->once())
            ->method('getBotToken')
            ->willReturn($botToken = 'botToken');

        $surface = $this->createMock(HomeSurface::class);
        $surface->expects($this->once())
            ->method('getWorkspace')
            ->willReturn($workspace);

        $surface->expects($this->once())
            ->method('toArray')
            ->willReturn($surfaceValue);

        $clientFactory = $this->createMock(ClientFactory::class);
        $clientFactory->expects($this->once())
            ->method('create')
            ->with($botToken)
            ->willReturn($client);

        $service = new HomeService(
            $clientFactory,
            $this->getLogger(),
        );

        $service->publish($surface);
    }
}

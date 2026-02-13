<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Surface\Service;

use App\Entity\Workspace;
use App\Slack\Client\Factory\ClientFactory;
use App\Slack\Surface\Component\ModalSurface;
use App\Slack\Surface\Service\ModalService;
use App\Tests\Unit\Slack\WithBlockAssertions;
use App\Tests\Unit\Slack\WithBlockElementAssertions;
use App\Tests\Unit\Slack\WithSurfaceAssertions;
use JoliCode\Slack\Api\Client;
use JoliCode\Slack\Api\Model\ViewsOpenPostResponse200;
use JoliCode\Slack\Exception\SlackErrorResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(ModalService::class)]
class ModalServiceTest extends KernelTestCase
{
    use WithSurfaceAssertions;
    use WithBlockAssertions;
    use WithBlockElementAssertions;

    #[Test]
    public function itShouldLogErrorIfWorkspaceBotTokenNotSet(): void
    {
        $workspace = $this->createMock(Workspace::class);
        $workspace->expects($this->once())
            ->method('getBotToken')
            ->willReturn(null);

        $workspace->expects($this->once())
            ->method('getName')
            ->willReturn($workspaceName = 'workspaceName');

        $client = $this->createMock(Client::class);
        $client->expects($this->never())
            ->method('viewsOpen')
            ->withAnyParameters();

        $clientFactory = $this->createMock(ClientFactory::class);
        $clientFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('error')
            ->with('Could not resolve bot token for workspace '.$workspaceName);

        $modal = $this->createStub(ModalSurface::class);

        $service = new ModalService($logger, $clientFactory);
        $service->createModal($modal, $workspace);
    }

    #[Test]
    public function itShouldLogErrorIfWorkspaceIsNull(): void
    {
        $client = $this->createMock(Client::class);
        $client->expects($this->never())
            ->method('viewsOpen')
            ->withAnyParameters();

        $clientFactory = $this->createMock(ClientFactory::class);
        $clientFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('error')
            ->with('Could not resolve bot token for workspace ');

        $modal = $this->createStub(ModalSurface::class);

        $service = new ModalService($logger, $clientFactory);
        $service->createModal($modal, null);
    }

    #[Test]
    public function itShouldLogErrorIfSlackErrorResponseThrown(): void
    {
        $modal = $this->createMock(ModalSurface::class);
        $modal->expects($this->once())
            ->method('toArray')
            ->willReturn($modalValue = []);

        $exception = $this->createMock(SlackErrorResponse::class);
        $exception->expects($this->once())
            ->method('getResponseMetadata')
            ->willReturn($responseMetadata = ['key' => 'value']);

        $workspace = $this->createMock(Workspace::class);
        $workspace->expects($this->once())
            ->method('getBotToken')
            ->willReturn($botToken = 'botToken');

        $callCount = 0;

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->exactly(2))
            ->method('error')
            ->withAnyParameters()
            ->willReturnCallback(function ($argument) use (&$callCount, $responseMetadata) {
                if (2 === ++$callCount) {
                    $this->assertIsString($argument);

                    $expectedArgument = json_encode($responseMetadata);

                    if (false !== $expectedArgument) {
                        $this->assertEquals($expectedArgument, $argument);
                    }
                }
            });

        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('viewsOpen')
            ->with($modalValue)
            ->willThrowException($exception);

        $clientFactory = $this->createMock(ClientFactory::class);
        $clientFactory->expects($this->once())
            ->method('create')
            ->with($botToken)
            ->willReturn($client);

        $service = new ModalService($logger, $clientFactory);
        $service->createModal($modal, $workspace);
    }

    #[Test]
    public function itShouldNotLogSuccessfulRequest(): void
    {
        $modal = $this->createMock(ModalSurface::class);
        $modal->expects($this->once())
            ->method('toArray')
            ->willReturn($modalValue = []);

        $workspace = $this->createMock(Workspace::class);
        $workspace->expects($this->once())
            ->method('getBotToken')
            ->willReturn($botToken = 'botToken');

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->never())
            ->method('error')
            ->withAnyParameters();

        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('viewsOpen')
            ->with($modalValue)
            ->willReturn($this->createStub(ViewsOpenPostResponse200::class));

        $clientFactory = $this->createMock(ClientFactory::class);
        $clientFactory->expects($this->once())
            ->method('create')
            ->with($botToken)
            ->willReturn($client);

        $service = new ModalService($logger, $clientFactory);
        $service->createModal($modal, $workspace);
    }

    #[Test]
    public function itShouldLogErrorIfWorkspaceIsNullOnUpdateModal(): void
    {
        $client = $this->createMock(Client::class);
        $client->expects($this->never())
            ->method('viewsOpen')
            ->withAnyParameters();

        $clientFactory = $this->createMock(ClientFactory::class);
        $clientFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('error')
            ->with('Could not resolve bot token for workspace ');

        $modal = $this->createStub(ModalSurface::class);

        $service = new ModalService($logger, $clientFactory);
        $service->updateModal($modal, null, 'viewId');
    }

    #[Test]
    public function itShouldLogErrorIfWorkspaceBotTokenNotSetOnUpdate(): void
    {
        $workspace = $this->createMock(Workspace::class);
        $workspace->expects($this->once())
            ->method('getBotToken')
            ->willReturn(null);

        $workspace->expects($this->once())
            ->method('getName')
            ->willReturn($workspaceName = 'workspaceName');

        $client = $this->createMock(Client::class);
        $client->expects($this->never())
            ->method('viewsOpen')
            ->withAnyParameters();

        $clientFactory = $this->createMock(ClientFactory::class);
        $clientFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('error')
            ->with('Could not resolve bot token for workspace '.$workspaceName);

        $modal = $this->createStub(ModalSurface::class);

        $service = new ModalService($logger, $clientFactory);
        $service->updateModal($modal, $workspace, 'viewId');
    }

    #[Test]
    public function itShouldNotLogSuccessfulRequestOnUpdate(): void
    {
        $modal = $this->createMock(ModalSurface::class);
        $modal->expects($this->once())
            ->method('toArray')
            ->willReturn($modalValue = []);

        $workspace = $this->createMock(Workspace::class);
        $workspace->expects($this->once())
            ->method('getBotToken')
            ->willReturn($botToken = 'botToken');

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->never())
            ->method('error')
            ->withAnyParameters();

        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('viewsUpdate')
            ->with([
                'view_id' => $viewId = 'viewId',
            ])
            ->willReturn($this->createStub(ViewsOpenPostResponse200::class));

        $clientFactory = $this->createMock(ClientFactory::class);
        $clientFactory->expects($this->once())
            ->method('create')
            ->with($botToken)
            ->willReturn($client);

        $service = new ModalService($logger, $clientFactory);
        $service->updateModal($modal, $workspace, $viewId);
    }

    #[Test]
    public function itShouldLogErrorIfSlackErrorResponseThrownOnUpdate(): void
    {
        $modal = $this->createMock(ModalSurface::class);
        $modal->expects($this->once())
            ->method('toArray')
            ->willReturn($modalValue = []);

        $exception = $this->createMock(SlackErrorResponse::class);
        $exception->expects($this->once())
            ->method('getResponseMetadata')
            ->willReturn($responseMetadata = ['key' => 'value']);

        $workspace = $this->createMock(Workspace::class);
        $workspace->expects($this->once())
            ->method('getBotToken')
            ->willReturn($botToken = 'botToken');

        $callCount = 0;

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->exactly(2))
            ->method('error')
            ->withAnyParameters()
            ->willReturnCallback(function ($argument) use (&$callCount, $responseMetadata) {
                if (2 === ++$callCount) {
                    $this->assertIsString($argument);

                    $expectedArgument = json_encode($responseMetadata);

                    if (false !== $expectedArgument) {
                        $this->assertEquals($expectedArgument, $argument);
                    }
                }
            });

        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('viewsUpdate')
            ->with(['view_id' => $viewId = 'viewId'])
            ->willThrowException($exception);

        $clientFactory = $this->createMock(ClientFactory::class);
        $clientFactory->expects($this->once())
            ->method('create')
            ->with($botToken)
            ->willReturn($client);

        $service = new ModalService($logger, $clientFactory);
        $service->updateModal($modal, $workspace, $viewId);
    }
}

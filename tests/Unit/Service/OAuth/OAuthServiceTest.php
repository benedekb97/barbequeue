<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\OAuth;

use App\Service\OAuth\OAuthService;
use App\Slack\Client\Factory\ClientFactory;
use JoliCode\Slack\Api\Client;
use JoliCode\Slack\Api\Model\OauthV2AccessGetResponse200;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(OAuthService::class)]
class OAuthServiceTest extends KernelTestCase
{
    #[Test]
    public function itShouldReturnCorrectRedirectUrl(): void
    {
        $service = new OAuthService(
            $this->createStub(ClientFactory::class),
            $clientId = 'clientId',
            '',
            $authUrl = 'authUrl',
            $scopes = ['scope1', 'scope2'],
        );

        $result = $service->getRedirectUrl();

        $this->assertEquals(
            $authUrl.'?client_id='.$clientId.'&scope='.implode('%2C', $scopes),
            $result
        );
    }

    #[Test]
    public function itShouldReturnOAuthAccessResponse(): void
    {
        $clientResponse = new OauthV2AccessGetResponse200([
            'team' => [
                'id' => $teamId = 'teamId',
                'name' => $teamName = 'teamName',
            ],
            'authed_user' => [
                'id' => $authedUserId = 'authedUserId',
            ],
            'access_token' => $accessToken = 'accessToken',
            'incoming_webhook' => [
                'channel_id' => $channelId = 'channelId',
            ],
        ]);

        $accessCode = 'accessCode';
        $clientId = 'clientId';
        $clientSecret = 'clientSecret';

        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('oauthV2Access')
            ->willReturnCallback(function ($argument) use ($clientResponse, $accessCode, $clientId, $clientSecret) {
                $this->assertIsArray($argument);
                $this->assertArrayHasKey('code', $argument);
                $this->assertEquals($accessCode, $argument['code']);

                $this->assertArrayHasKey('client_id', $argument);
                $this->assertEquals($clientId, $argument['client_id']);

                $this->assertArrayHasKey('client_secret', $argument);
                $this->assertEquals($clientSecret, $argument['client_secret']);

                return $clientResponse;
            });

        $clientFactory = $this->createMock(ClientFactory::class);
        $clientFactory->expects($this->once())
            ->method('create')
            ->willReturn($client);

        $service = new OAuthService(
            $clientFactory,
            $clientId,
            $clientSecret,
            '',
            []
        );

        $response = $service->authorise($accessCode);

        $this->assertEquals($accessToken, $response->getAccessToken());
        $this->assertEquals($teamId, $response->getTeamId());
        $this->assertEquals($teamName, $response->getTeamName());
        $this->assertEquals($authedUserId, $response->getUserId());
        $this->assertEquals($channelId, $response->getBotChannelId());
    }
}

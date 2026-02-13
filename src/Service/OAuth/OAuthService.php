<?php

declare(strict_types=1);

namespace App\Service\OAuth;

use App\Slack\Client\Factory\ClientFactory;
use JoliCode\Slack\Api\Model\OauthV2AccessGetResponse200;

readonly class OAuthService
{
    public function __construct(
        private ClientFactory $clientFactory,
        private string $slackClientId,
        #[\SensitiveParameter]
        private string $slackClientSecret,
        private string $slackAuthUrl,
        private array $slackScopes,
    ) {
    }

    public function getRedirectUrl(): string
    {
        return sprintf(
            '%s?%s',
            $this->slackAuthUrl,
            http_build_query([
                'client_id' => $this->slackClientId,
                'scope' => implode(',', $this->slackScopes),
            ]),
        );
    }

    public function authorise(string $accessCode): OAuthAccessResponse
    {
        /** @var OauthV2AccessGetResponse200 $response */
        $response = $this->clientFactory->create('')->oauthV2Access([
            'code' => $accessCode,
            'client_id' => $this->slackClientId,
            'client_secret' => $this->slackClientSecret,
        ]);

        return new OAuthAccessResponse(
            $this->getTeamName($response),
            $this->getTeamId($response),
            $this->getUserId($response),
            $this->getAccessToken($response),
            $this->getBotChannelId($response),
        );
    }

    private function getTeamName(OauthV2AccessGetResponse200 $response): string
    {
        /** @var array{name: string} $team */
        $team = $response['team'];

        return $team['name'];
    }

    private function getTeamId(OauthV2AccessGetResponse200 $response): string
    {
        /** @var array{id: string} $team */
        $team = $response['team'];

        return $team['id'];
    }

    private function getUserId(OauthV2AccessGetResponse200 $response): string
    {
        /** @var array{id: string} $authedUser */
        $authedUser = $response['authed_user'];

        return $authedUser['id'];
    }

    private function getAccessToken(OauthV2AccessGetResponse200 $response): string
    {
        /** @var string $accessToken */
        $accessToken = $response['access_token'];

        return $accessToken;
    }

    private function getBotChannelId(OauthV2AccessGetResponse200 $response): string
    {
        /** @var array{channel_id: string} $incomingWebhook */
        $incomingWebhook = $response['incoming_webhook'];

        return $incomingWebhook['channel_id'];
    }
}

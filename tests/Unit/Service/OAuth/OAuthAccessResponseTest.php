<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\OAuth;

use App\Service\OAuth\OAuthAccessResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(OAuthAccessResponse::class)]
class OAuthAccessResponseTest extends KernelTestCase
{
    #[Test]
    public function itShouldReturnPassedParameters(): void
    {
        $response = new OAuthAccessResponse(
            $teamName = 'teamName',
            $teamId = 'teamId',
            $userId = 'userId',
            $accessToken = 'accessToken',
            $channelId = 'channelId',
        );

        $this->assertSame($teamName, $response->getTeamName());
        $this->assertSame($teamId, $response->getTeamId());
        $this->assertSame($userId, $response->getUserId());
        $this->assertSame($accessToken, $response->getAccessToken());
        $this->assertSame($channelId, $response->getBotChannelId());
    }
}

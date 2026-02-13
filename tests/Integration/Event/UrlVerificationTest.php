<?php

declare(strict_types=1);

namespace App\Tests\Integration\Event;

use App\Slack\Event\Event;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

class UrlVerificationTest extends WebTestCase
{
    #[Test]
    public function itShouldReturnChallengePassed(): void
    {
        $client = static::createClient();

        $client->request(Request::METHOD_POST, '/api/slack/event', [
            'type' => Event::URL_VERIFICATION->value,
            'token' => 'token',
            'challenge' => $challenge = 'challenge',
        ]);

        $response = $client->getResponse();

        $this->assertResponseIsSuccessful();

        $response = $response->getContent();

        $this->assertIsString($response);

        $response = json_decode($response, true);

        $this->assertIsArray($response);
        $this->assertArrayHasKey('challenge', $response);
        $this->assertEquals($challenge, $response['challenge']);
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Response\Interaction;

use App\Slack\Response\Interaction\InteractionResponseHandler;
use App\Slack\Response\Interaction\SlackInteractionResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[CoversClass(InteractionResponseHandler::class)]
class InteractionResponseHandlerTest extends KernelTestCase
{
    #[Test]
    public function itShouldLogExceptions(): void
    {
        $response = $this->createMock(SlackInteractionResponse::class);
        $response->expects($this->once())
            ->method('toArray')
            ->willReturn($responseData = []);

        $exception = new \Exception();

        $client = $this->createMock(HttpClientInterface::class);
        $client->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                $url = 'url',
                [
                    'body' => json_encode($responseData, JSON_UNESCAPED_SLASHES),
                    'headers' => [
                        'Content-Type' => 'application/json',
                    ],
                ],
            )
            ->willThrowException($exception);

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->exactly(2))
            ->method('debug')
            ->withAnyParameters();

        $handler = new InteractionResponseHandler($client, $logger);
        $handler->handle($url, $response);
    }
}

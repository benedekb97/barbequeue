<?php

declare(strict_types=1);

namespace App\Tests\Integration\Command;

use App\DataFixtures\Queue\Queues;
use App\Repository\QueueRepositoryInterface;
use App\Tests\Unit\Slack\WithBlockAssertions;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class JoinQueueCommandTest extends WebTestCase
{
    use WithBlockAssertions;

    #[Test]
    public function itShouldSendQueueJoinedResponseToResponseUrl(): void
    {
        $client = static::createClient();

        $responseUrl = 'responseUrl';
        $queue = Queues::NO_EXPIRY_NO_USER_LIMIT;
        $workspace = $queue->getTeamId();

        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->expects($this->once())
            ->method('request')
            ->willReturnCallback(function ($method, $url, array $options = []) use ($responseUrl, $queue) {
                $this->assertEquals('POST', $method);
                $this->assertEquals($responseUrl, $url);

                $this->assertArrayHasKey('headers', $options);
                $this->assertIsArray($options['headers']);
                $this->assertArrayHasKey('Content-Type', $options['headers']);
                $this->assertEquals('application/json', $options['headers']['Content-Type']);

                $this->assertArrayHasKey('body', $options);
                $this->assertIsString($body = $options['body']);

                $body = json_decode($body, true);

                $this->assertIsArray($body);
                $this->assertArrayHasKey('blocks', $body);
                $this->assertIsArray($blocks = $body['blocks']);

                $this->assertCount(3, $blocks);
                $this->assertHeaderBlockCorrectlyFormatted(
                    'You have been added to the '.$queue->value.' queue.',
                    $blocks[0],
                );
                $this->assertDividerBlockCorrectlyFormatted($blocks[1]);
                $this->assertSectionBlockCorrectlyFormatted(
                    'You are the 1st, 2nd, 3rd and 4th in the '.$queue->value.' queue.',
                    $blocks[2],
                );
            });

        static::getContainer()->set(HttpClientInterface::class, $httpClient);

        $client->request('POST', '/api/slack/command', [
            'response_url' => $responseUrl,
            'command' => '/bbq',
            'text' => 'join '.$queue->value,
            'team_id' => $workspace->value,
            'user_id' => 'userId',
        ]);

        $this->assertResponseIsSuccessful();

        $response = $client->getResponse();

        $this->assertEmpty($response->getContent());

        /** @var QueueRepositoryInterface $queueRepository */
        $queueRepository = static::getContainer()->get(QueueRepositoryInterface::class);

        $queue = $queueRepository->findOneByNameAndTeamId($queue->value, $workspace->value);

        $this->assertNotNull($queue);
        $this->assertCount(4, $queue->getQueuedUsers());
    }

    #[Test, DataProvider('provideRequiredTime')]
    public function itShouldSendQueueJoinedResponseToResponseUrlAndSaveRequiredTime(
        string $requiredTime,
        ?int $expectedRequiredMinutes,
        Queues $queueTemplate,
    ): void {
        $client = static::createClient();

        $responseUrl = 'responseUrl';
        $workspace = $queueTemplate->getTeamId();

        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->expects($this->once())
            ->method('request')
            ->willReturnCallback(function ($method, $url, array $options = []) use ($responseUrl, $queueTemplate) {
                $this->assertEquals('POST', $method);
                $this->assertEquals($responseUrl, $url);

                $this->assertArrayHasKey('headers', $options);
                $this->assertIsArray($options['headers']);
                $this->assertArrayHasKey('Content-Type', $options['headers']);
                $this->assertEquals('application/json', $options['headers']['Content-Type']);

                $this->assertArrayHasKey('body', $options);
                $this->assertIsString($body = $options['body']);

                $body = json_decode($body, true);

                $this->assertIsArray($body);
                $this->assertArrayHasKey('blocks', $body);
                $this->assertIsArray($blocks = $body['blocks']);

                $this->assertCount(3, $blocks);
                $this->assertHeaderBlockCorrectlyFormatted(
                    'You have been added to the '.$queueTemplate->value.' queue.',
                    $blocks[0],
                );
                $this->assertDividerBlockCorrectlyFormatted($blocks[1]);
                $this->assertSectionBlockCorrectlyFormatted(
                    'You are the 1st, 2nd, 3rd and 4th in the '.$queueTemplate->value.' queue.',
                    $blocks[2],
                );
            });

        static::getContainer()->set(HttpClientInterface::class, $httpClient);

        $client->request('POST', '/api/slack/command', [
            'response_url' => $responseUrl,
            'command' => '/bbq',
            'text' => 'join '.$queueTemplate->value.' '.$requiredTime,
            'team_id' => $workspace->value,
            'user_id' => $userId = 'userId',
        ]);

        $this->assertResponseIsSuccessful();

        $response = $client->getResponse();

        $this->assertEmpty($response->getContent());

        /** @var QueueRepositoryInterface $queueRepository */
        $queueRepository = static::getContainer()->get(QueueRepositoryInterface::class);

        $queue = $queueRepository->findOneByNameAndTeamId($queueTemplate->value, $workspace->value);

        $this->assertNotNull($queue);
        $this->assertCount($queueTemplate->getInitialUserCount() + 1, $queue->getQueuedUsers());

        $lastUser = $queue->getLastPlace($userId);

        $this->assertNotNull($lastUser);

        $this->assertEquals($expectedRequiredMinutes, $lastUser->getExpiryMinutes());
    }

    public static function provideRequiredTime(): array
    {
        return [
            ['1h', 60, Queues::NO_EXPIRY_NO_USER_LIMIT],
            ['1h10m', 70, Queues::NO_EXPIRY_NO_USER_LIMIT],
            ['1h0m', 60, Queues::NO_EXPIRY_NO_USER_LIMIT],
            ['1h 0m', 60, Queues::NO_EXPIRY_NO_USER_LIMIT],
            ['1h 10m', 70, Queues::NO_EXPIRY_NO_USER_LIMIT],
            ['60m', 60, Queues::NO_EXPIRY_NO_USER_LIMIT],
            ['70m', 70, Queues::NO_EXPIRY_NO_USER_LIMIT],
            ['0m', null, Queues::NO_EXPIRY_NO_USER_LIMIT],
            ['', null, Queues::NO_EXPIRY_NO_USER_LIMIT],
            ['1h', 15, Queues::FIFTEEN_MINUTE_EXPIRY_NO_USER_LIMIT],
            ['1h10m', 15, Queues::FIFTEEN_MINUTE_EXPIRY_NO_USER_LIMIT],
            ['1h0m', 15, Queues::FIFTEEN_MINUTE_EXPIRY_NO_USER_LIMIT],
            ['1h 0m', 15, Queues::FIFTEEN_MINUTE_EXPIRY_NO_USER_LIMIT],
            ['1h 10m', 15, Queues::FIFTEEN_MINUTE_EXPIRY_NO_USER_LIMIT],
            ['60m', 15, Queues::FIFTEEN_MINUTE_EXPIRY_NO_USER_LIMIT],
            ['70m', 15, Queues::FIFTEEN_MINUTE_EXPIRY_NO_USER_LIMIT],
            ['10m', 10, Queues::FIFTEEN_MINUTE_EXPIRY_NO_USER_LIMIT],
            ['5', 5, Queues::FIFTEEN_MINUTE_EXPIRY_NO_USER_LIMIT],
            ['0m', 15, Queues::FIFTEEN_MINUTE_EXPIRY_NO_USER_LIMIT],
            ['', 15, Queues::FIFTEEN_MINUTE_EXPIRY_NO_USER_LIMIT],
        ];
    }
}

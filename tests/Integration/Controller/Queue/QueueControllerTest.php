<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller\Queue;

use App\Controller\Queue\QueueController;
use App\Enum\QueueBehaviour;
use App\Tests\Integration\ApiTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(QueueController::class)]
class QueueControllerTest extends ApiTestCase
{
    #[Test]
    public function itShouldListQueues(): void
    {
        $this->jsonGet('/api/queue');
        $this->assertOk();

        $response = $this->getJsonResponse();

        $this->assertCount(0, $response);

        $this->jsonPost('/api/queue/', [
            'type' => 'simple',
            'name' => $queueName = 'queue',
        ], administrator: true);
        $this->assertCreated();

        $this->jsonGet('/api/queue');
        $this->assertOk();

        $response = $this->getJsonResponse();

        $this->assertCount(1, $response);

        $queue = $response[0];

        $this->assertIsArray($queue);

        $this->assertArrayHasKey('name', $queue);
        $this->assertEquals($queueName, $queue['name']);

        $this->assertArrayHasKey('queuedUsers', $queue);
        $this->assertIsArray($queue['queuedUsers']);
        $this->assertEmpty($queue['queuedUsers']);

        $this->assertArrayHasKey('expiryMinutes', $queue);
        $this->assertNull($queue['expiryMinutes']);

        $this->assertArrayHasKey('maximumEntriesPerUser', $queue);
        $this->assertNull($queue['maximumEntriesPerUser']);
    }

    #[Test]
    public function itShouldShowQueue(): void
    {
        $this->jsonGet('/api/queue/queueName');
        $this->assertNotFound();

        $this->jsonPost('/api/queue/', [
            'type' => 'simple',
            'name' => $queueName = 'queueName',
            'expiryMinutes' => $expiry = 5,
            'maximumEntriesPerUser' => $maxEntries = 2,
        ], administrator: true);
        $this->assertCreated();

        $this->jsonGet('/api/queue/queueName');
        $this->assertOk();

        $response = $this->getJsonResponse();

        $this->assertArrayHasKey('name', $response);
        $this->assertEquals($queueName, $response['name']);

        $this->assertArrayHasKey('queuedUsers', $response);
        $this->assertIsArray($response['queuedUsers']);
        $this->assertEmpty($response['queuedUsers']);

        $this->assertArrayHasKey('expiryMinutes', $response);
        $this->assertEquals($expiry, $response['expiryMinutes']);

        $this->assertArrayHasKey('maximumEntriesPerUser', $response);
        $this->assertEquals($maxEntries, $response['maximumEntriesPerUser']);
    }

    #[Test]
    public function itShouldCreateDeploymentQueue(): void
    {
        $this->jsonGet('/api/queue/queueName');
        $this->assertNotFound();

        $this->jsonPost('/api/queue/', [
            'type' => 'deployment',
            'behaviour' => $behaviour = 'allow-jumps',
            'repositories' => [$repositoryId = $this->createRepository()],
            'name' => $queueName = 'queueName',
            'expiryMinutes' => $expiry = 5,
            'maximumEntriesPerUser' => $maxEntries = 2,
        ], administrator: true);
        $this->assertCreated();

        $this->jsonGet('/api/queue/queueName');
        $this->assertOk();

        $response = $this->getJsonResponse();

        $this->assertArrayHasKey('name', $response);
        $this->assertEquals($queueName, $response['name']);

        $this->assertArrayHasKey('queuedUsers', $response);
        $this->assertIsArray($response['queuedUsers']);
        $this->assertEmpty($response['queuedUsers']);

        $this->assertArrayHasKey('behaviour', $response);
        $this->assertEquals($behaviour, $response['behaviour']);

        $this->assertArrayHasKey('repositories', $response);
        $this->assertIsArray($response['repositories']);
        $this->assertContains(['id' => $repositoryId, 'name' => 'repository'], $response['repositories']);

        $this->assertArrayHasKey('expiryMinutes', $response);
        $this->assertEquals($expiry, $response['expiryMinutes']);

        $this->assertArrayHasKey('maximumEntriesPerUser', $response);
        $this->assertEquals($maxEntries, $response['maximumEntriesPerUser']);
    }

    #[Test]
    public function itShouldReturnValidationErrorIfMissingFieldOnCreate(): void
    {
        $this->jsonPost('/api/queue/', [], administrator: true);
        $this->assertUnprocessable();

        $response = $this->getJsonResponse();

        $this->assertArrayHasKey('errors', $response);
        $this->assertIsArray($errors = $response['errors']);
        $this->assertCount(2, $errors);

        $this->jsonPost('/api/queue/', [
            'type' => 'deployment',
        ], administrator: true);
        $this->assertUnprocessable();

        $response = $this->getJsonResponse();

        $this->assertArrayHasKey('title', $response);
        $this->assertEquals('Validation Failed', $response['title']);
    }

    #[Test]
    public function itShouldUpdateQueue(): void
    {
        $this->jsonPatch('/api/queue/queueName', administrator: true);
        $this->assertNotFound();

        $this->jsonPost('/api/queue/', [
            'type' => 'deployment',
            'behaviour' => 'allow-jumps',
            'repositories' => [$repository = $this->createRepository()],
            'name' => $queueName = 'queueName',
            'expiryMinutes' => 5,
            'maximumEntriesPerUser' => 2,
        ], administrator: true);
        $this->assertCreated();

        $this->jsonPatch('/api/queue/'.$queueName, [
            'type' => 'deployment',
            'name' => $queueName = 'newQueueName',
            'expiryMinutes' => $expiry = 10,
            'maximumEntriesPerUser' => $maxEntries = 1,
            'behaviour' => $behaviour = QueueBehaviour::ALLOW_SIMULTANEOUS->value,
            'repositories' => [$repository],
        ], administrator: true);
        $this->assertOk();

        $response = $this->getJsonResponse();

        $this->assertArrayHasKey('name', $response);
        $this->assertEquals($queueName, $response['name']);

        $this->assertArrayHasKey('queuedUsers', $response);
        $this->assertIsArray($response['queuedUsers']);
        $this->assertEmpty($response['queuedUsers']);

        $this->assertArrayHasKey('behaviour', $response);
        $this->assertEquals($behaviour, $response['behaviour']);

        $this->assertArrayHasKey('repositories', $response);
        $this->assertIsArray($response['repositories']);
        $this->assertContains(['id' => $repository, 'name' => 'repository'], $response['repositories']);

        $this->assertArrayHasKey('expiryMinutes', $response);
        $this->assertEquals($expiry, $response['expiryMinutes']);

        $this->assertArrayHasKey('maximumEntriesPerUser', $response);
        $this->assertEquals($maxEntries, $response['maximumEntriesPerUser']);
    }

    #[Test]
    public function itShouldDeleteQueue(): void
    {
        $this->jsonDelete('/api/queue/queueName', administrator: true);
        $this->assertNotFound();

        $this->jsonPost('/api/queue/', [
            'type' => 'deployment',
            'behaviour' => 'allow-jumps',
            'repositories' => [$this->createRepository()],
            'name' => $queueName = 'queueName',
            'expiryMinutes' => 5,
            'maximumEntriesPerUser' => 2,
        ], administrator: true);
        $this->assertCreated();

        $this->jsonDelete('/api/queue/'.$queueName, administrator: true);
        $this->assertNoContent();

        $this->jsonGet('/api/queue/queueName');
        $this->assertNotFound();
    }
}

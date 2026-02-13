<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller;

use App\Controller\RepositoryController;
use App\Tests\Integration\ApiTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(RepositoryController::class)]
class RepositoryControllerTest extends ApiTestCase
{
    #[Test]
    public function itShouldListRepositories(): void
    {
        $this->jsonGet('/api/repository');
        $this->assertOk();
        $this->assertEmpty($this->getJsonResponse());

        $this->jsonPost('/api/repository/', [
            'name' => $repositoryName = 'name',
        ], administrator: true);
        $this->assertCreated();

        $this->jsonGet('/api/repository');
        $this->assertOk();
        $this->assertNotEmpty($response = $this->getJsonResponse());
        $this->assertCount(1, $response);
        $repository = $response[0];

        $this->assertIsArray($repository);
        $this->assertArrayHasKey('name', $repository);
        $this->assertEquals($repositoryName, $repository['name']);

        $this->assertArrayHasKey('url', $repository);
        $this->assertNull($repository['url']);

        $this->assertArrayHasKey('deploymentBlocksRepositories', $repository);
        $this->assertIsArray($repository['deploymentBlocksRepositories']);
        $this->assertEmpty($repository['deploymentBlocksRepositories']);

        $this->assertArrayHaskey('deploymentQueues', $repository);
        $this->assertIsArray($repository['deploymentQueues']);
        $this->assertEmpty($repository['deploymentQueues']);

        $this->assertArrayHasKey('deployments', $repository);
        $this->assertIsArray($repository['deployments']);
        $this->assertEmpty($repository['deployments']);
    }

    #[Test]
    public function itShouldShowRepository(): void
    {
        $this->jsonGet('/api/repository/0');
        $this->assertNotFound();

        $this->jsonPost('/api/repository/', [
            'name' => $repositoryName = 'repositoryName',
        ], administrator: true);
        $this->assertCreated();
        $this->assertNotEmpty($response = $this->getJsonResponse());
        $this->assertArrayHasKey('id', $response);
        $this->assertisInt($id = $response['id']);

        $this->jsonGet('/api/repository/'.$id);
        $this->assertOk();
        $this->assertNotEmpty($repository = $this->getJsonResponse());
        $this->assertArrayHasKey('name', $repository);
        $this->assertEquals($repositoryName, $repository['name']);

        $this->assertArrayHasKey('url', $repository);
        $this->assertNull($repository['url']);

        $this->assertArrayHasKey('deploymentBlocksRepositories', $repository);
        $this->assertIsArray($repository['deploymentBlocksRepositories']);
        $this->assertEmpty($repository['deploymentBlocksRepositories']);

        $this->assertArrayHaskey('deploymentQueues', $repository);
        $this->assertIsArray($repository['deploymentQueues']);
        $this->assertEmpty($repository['deploymentQueues']);

        $this->assertArrayHasKey('deployments', $repository);
        $this->assertIsArray($repository['deployments']);
        $this->assertEmpty($repository['deployments']);
    }

    #[Test]
    public function itShouldCreateRepository(): void
    {
        $this->jsonPost('/api/repository/', [
            'name' => $firstRepositoryName = 'repository-1',
            'url' => $url = 'https://example.com',
        ], administrator: true);
        $this->assertCreated();

        $this->assertNotEmpty($repository = $this->getJsonResponse());
        $this->assertArrayHasKey('id', $repository);
        $this->assertIsInt($id = $repository['id']);

        $this->assertArrayHasKey('name', $repository);
        $this->assertEquals($firstRepositoryName, $repository['name']);

        $this->assertArrayHasKey('url', $repository);
        $this->assertEquals($url, $repository['url']);

        $this->assertArrayHasKey('deploymentBlocksRepositories', $repository);
        $this->assertIsArray($repository['deploymentBlocksRepositories']);
        $this->assertEmpty($repository['deploymentBlocksRepositories']);

        $this->assertArrayHaskey('deploymentQueues', $repository);
        $this->assertIsArray($repository['deploymentQueues']);
        $this->assertEmpty($repository['deploymentQueues']);

        $this->assertArrayHasKey('deployments', $repository);
        $this->assertIsArray($repository['deployments']);
        $this->assertEmpty($repository['deployments']);

        $this->jsonPost('/api/repository/', [
            'name' => $secondRepositoryName = 'repository-2',
            'url' => $url,
            'deploymentBlocksRepositories' => [$id],
        ], administrator: true);
        $this->assertCreated();

        $this->assertNotEmpty($repository = $this->getJsonResponse());
        $this->assertArrayHasKey('id', $repository);
        $this->assertIsInt($repository['id']);

        $this->assertArrayHasKey('name', $repository);
        $this->assertEquals($secondRepositoryName, $repository['name']);

        $this->assertArrayHasKey('url', $repository);
        $this->assertEquals($url, $repository['url']);

        $this->assertArrayHasKey('deploymentBlocksRepositories', $repository);
        $this->assertIsArray($repository['deploymentBlocksRepositories']);
        $this->assertContains([
            'id' => $id,
            'name' => $firstRepositoryName,
        ], $repository['deploymentBlocksRepositories']);

        $this->assertArrayHaskey('deploymentQueues', $repository);
        $this->assertIsArray($repository['deploymentQueues']);
        $this->assertEmpty($repository['deploymentQueues']);

        $this->assertArrayHasKey('deployments', $repository);
        $this->assertIsArray($repository['deployments']);
        $this->assertEmpty($repository['deployments']);
    }

    #[Test]
    public function itShouldReturnValidationErrorIfNameEmpty(): void
    {
        $this->jsonPost('/api/repository/', [
            'url' => 'https://example.com',
        ], administrator: true);
        $this->assertUnprocessable();

        $this->assertNotEmpty($response = $this->getJsonResponse());

        $this->assertArrayHasKey('title', $response);
        $this->assertEquals('Validation Failed', $response['title']);
    }

    #[Test]
    public function itShouldUpdateRepository(): void
    {
        $this->jsonPatch('/api/repository/0', administrator: true);
        $this->assertNotFound();

        $this->jsonPost('/api/repository/', [
            'name' => 'repositoryName',
        ], administrator: true);
        $this->assertCreated();
        $this->assertNotEmpty($response = $this->getJsonResponse());
        $this->assertArrayHasKey('id', $response);
        $this->assertIsInt($id = $response['id']);

        $this->jsonPatch('/api/repository/'.$id, [
            'name' => $repositoryName = 'newRepositoryName',
            'url' => $url = 'https://example.com',
        ], administrator: true);
        $this->assertOk();

        $this->assertNotEmpty($repository = $this->getJsonResponse());
        $this->assertArrayHasKey('id', $repository);
        $this->assertEquals($id, $repository['id']);

        $this->assertArrayHasKey('name', $repository);
        $this->assertEquals($repositoryName, $repository['name']);

        $this->assertArrayHasKey('url', $repository);
        $this->assertEquals($url, $repository['url']);
    }

    #[Test]
    public function itShouldReturnValidationErrorIfNameEmptyOnPut(): void
    {
        $this->jsonPost('/api/repository/', [
            'name' => 'repositoryName',
        ], administrator: true);
        $this->assertCreated();
        $this->assertNotEmpty($response = $this->getJsonResponse());
        $this->assertArrayHasKey('id', $response);
        $this->assertIsInt($id = $response['id']);

        $this->jsonPut('/api/repository/'.$id, administrator: true);
        $this->assertUnprocessable();

        $this->assertNotEmpty($response = $this->getJsonResponse());

        $this->assertArrayHasKey('title', $response);
        $this->assertEquals('Validation Failed', $response['title']);
    }

    #[Test]
    public function itShouldReturnValidationErrorOnPutIfBlockedRepositoryInvalid(): void
    {
        $this->jsonPost('/api/repository/', [
            'name' => 'repositoryName',
        ], administrator: true);
        $this->assertCreated();
        $this->assertNotEmpty($response = $this->getJsonResponse());
        $this->assertArrayHasKey('id', $response);
        $this->assertIsInt($id = $response['id']);

        $this->jsonPut('/api/repository/'.$id, [
            'name' => 'name',
            'deploymentBlocksRepositories' => [0],
        ], administrator: true);
        $this->assertUnprocessable();

        $this->assertNotEmpty($response = $this->getJsonResponse());

        $this->assertArrayHasKey('title', $response);
        $this->assertEquals('Validation Failed', $response['title']);
    }

    #[Test]
    public function itShouldDeleteRepository(): void
    {
        $this->jsonDelete('/api/repository/0', administrator: true);
        $this->assertNotFound();

        $this->jsonPost('/api/repository/', [
            'name' => 'repositoryName',
        ], administrator: true);
        $this->assertCreated();
        $this->assertNotEmpty($response = $this->getJsonResponse());
        $this->assertArrayHasKey('id', $response);
        $this->assertIsInt($id = $response['id']);

        $this->jsonDelete('/api/repository/'.$id, administrator: true);
        $this->assertNoContent();

        $this->jsonGet('/api/repository/'.$id);
        $this->assertNotFound();
    }
}

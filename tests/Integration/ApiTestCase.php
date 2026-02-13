<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use App\Entity\Administrator;
use App\Entity\NotificationSettings;
use App\Entity\Repository;
use App\Entity\User;
use App\Entity\Workspace;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\AccessToken\AccessTokenHandlerInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;

class ApiTestCase extends WebTestCase
{
    private const string ADMINISTRATOR = 'administrator';
    private const string USER = 'user';

    private KernelBrowser $client;

    private Workspace $workspace;

    private User $user;

    protected User $administrator;

    private EntityManagerInterface $entityManager;

    private bool $setUpComplete = false;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);

        $this->entityManager = $entityManager;

        $this->getWorkspace();

        $this->entityManager->flush();

        $this->client->followRedirects();
        $this->client->disableReboot();

        $this->getUser();
        $this->getAdministrator();

        $this->entityManager->flush();

        $this->entityManager->refresh($this->administrator);
        $this->entityManager->refresh($this->user);
        $this->entityManager->refresh($this->workspace);
    }

    protected function jsonGet(string $url, array $parameters = [], bool $administrator = false): void
    {
        $this->setUpRequest();

        $this->client->request('GET', $url, $parameters, server: $this->getHeaders($administrator));
    }

    protected function jsonPost(string $url, array $parameters = [], bool $administrator = false): void
    {
        $this->setUpRequest();

        $this->client->request('POST', $url, $parameters, server: $this->getHeaders($administrator));
    }

    protected function jsonPut(string $url, array $parameters = [], bool $administrator = false): void
    {
        $this->setUpRequest();

        $this->client->request('PUT', $url, $parameters, server: $this->getHeaders($administrator));
    }

    protected function jsonPatch(string $url, array $parameters = [], bool $administrator = false): void
    {
        $this->setUpRequest();

        $this->client->request('PATCH', $url, $parameters, server: $this->getHeaders($administrator));
    }

    protected function jsonDelete(string $url, array $parameters = [], bool $administrator = false): void
    {
        $this->setUpRequest();

        $this->client->request('DELETE', $url, $parameters, server: $this->getHeaders($administrator));
    }

    protected function getJsonResponse(): array
    {
        $response = json_decode($this->client->getResponse()->getContent() ?: '', true);

        $this->assertIsArray($response);

        return $response;
    }

    protected function assertOk(): void
    {
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    protected function assertCreated(): void
    {
        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
    }

    protected function assertNoContent(): void
    {
        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
    }

    protected function assertNotFound(): void
    {
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    protected function assertUnprocessable(): void
    {
        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    protected function assertUnauthorized(): void
    {
        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    protected function assertValidationErrorExistsAtProperty(string $property, string $message): void
    {
        $this->assertArrayHasKey('children', $response = $this->getJsonResponse());
        $this->assertIsArray($response['children']);
        $this->assertArrayHasKey($property, $response['children']);
        $this->assertisArray($propertyErrors = $response['children'][$property]);
        $this->assertArrayHasKey('errors', $propertyErrors);
        $this->assertIsArray($propertyErrors['errors']);
        $this->assertContains([
            'message' => $message,
            'cause' => [],
        ], $propertyErrors['errors']);
    }

    protected function extractIdFromResponse(): int
    {
        $response = $this->getJsonResponse();
        $this->assertArrayHasKey('id', $response);
        $this->assertIsInt($response['id']);

        return $response['id'];
    }

    protected function createRepository(string $name = 'repository'): int
    {
        $repository = new Repository()
            ->setName($name)
            ->setWorkspace($this->getWorkspace());

        $this->entityManager->persist($repository);
        $this->entityManager->flush();

        return (int) $repository->getId();
    }

    private function getHeaders(bool $administrator): array
    {
        return [
            'HTTP_AUTHORIZATION' => 'Bearer '.($administrator ? self::ADMINISTRATOR : self::USER),
        ];
    }

    private function setUpRequest(): void
    {
        if ($this->setUpComplete) {
            return;
        }

        $accessTokenHandler = $this->createStub(AccessTokenHandlerInterface::class);
        $accessTokenHandler->method('getUserBadgeFrom')->willReturnCallback(function (string $userIdentifier) {
            $attributes = [
                'https://slack.com/user_id' => $userIdentifier,
                'https://slack.com/team_id' => $this->getWorkspace()->getSlackId(),
                'name' => 'name',
                'sub' => $userIdentifier,
            ];

            return new UserBadge(
                $userIdentifier,
                null,
                $attributes,
            );
        });

        static::getContainer()->set('security.access_token_handler.api', $accessTokenHandler);

        $this->setUpComplete = true;
    }

    private function getWorkspace(): Workspace
    {
        if (isset($this->workspace)) {
            return $this->workspace;
        }

        $workspace = new Workspace()
            ->setName('workspace')
            ->setSlackId('workspace')
            ->setBotToken('botToken');

        $this->entityManager->persist($workspace);

        return $this->workspace = $workspace;
    }

    protected function getUser(): User
    {
        if (isset($this->user)) {
            return $this->user;
        }

        $this->user = $user = new User()
            ->setWorkspace($this->getWorkspace())
            ->setNotificationSettings(new NotificationSettings())
            ->setSlackId(self::USER);

        $this->entityManager->persist($user);

        return $user;
    }

    protected function getAdministrator(): User
    {
        if (isset($this->administrator)) {
            return $this->administrator;
        }

        $administrator = new Administrator()
            ->setWorkspace($this->getWorkspace());

        $this->administrator = $user = new User()
            ->setWorkspace($this->getWorkspace())
            ->setNotificationSettings(new NotificationSettings())
            ->setSlackId(self::ADMINISTRATOR)
            ->setAdministrator($administrator);

        $administrator->setUser($user);

        $this->entityManager->persist($administrator);
        $this->entityManager->persist($user);

        return $user;
    }
}
